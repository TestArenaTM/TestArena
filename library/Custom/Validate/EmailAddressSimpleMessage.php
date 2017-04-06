<?php
class Custom_Validate_EmailAddressSimpleMessage extends Zend_Validate_EmailAddress
{
  const COMMON_ERROR = 'emailAddressInvalid';
  
  /**
   * Returns if the given host is reserved
   *
   * @param string $host
   * @return boolean
   */
  private function _isReserved($host)
  {
    if (!preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $host))
    {
      $host = gethostbyname($host);
    }

    $octet = explode('.',$host);
    
    if ((int)$octet[0] >= 224)
    {
      return true;
    }
    else if (array_key_exists($octet[0], $this->_invalidIp))
    {
      foreach ((array)$this->_invalidIp[$octet[0]] as $subnetData)
      {
        // we skip the first loop as we already know that octet matches
        for ($i = 1; $i < 4; $i++)
        {
          if (strpos($subnetData, $octet[$i]) !== $i * 4)
          {
            break;
          }
        }

        $host       = explode("/", $subnetData);
        $binaryHost = "";
        $tmp        = explode(".", $host[0]);
        
        for ($i = 0; $i < 4 ; $i++)
        {
          $binaryHost .= str_pad(decbin($tmp[$i]), 8, "0", STR_PAD_LEFT);
        }

        $segmentData = array(
          'network'   => (int)$this->_toIp(str_pad(substr($binaryHost, 0, $host[1]), 32, 0)),
          'broadcast' => (int)$this->_toIp(str_pad(substr($binaryHost, 0, $host[1]), 32, 1))
        );

        for ($j = $i; $j < 4; $j++)
        {
          if ((int)$octet[$j] < $segmentData['network'][$j] ||
              (int)$octet[$j] > $segmentData['broadcast'][$j])
          {
            return false;
          }
        }
      }

      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Converts a binary string to an IP address
   *
   * @param string $binary
   * @return mixed
   */
  private function _toIp($binary)
  {
      $ip  = array();
      $tmp = explode(".", chunk_split($binary, 8, "."));
  
      for ($i = 0; $i < 4 ; $i++)
      {
          $ip[$i] = bindec($tmp[$i]);
      }

      return $ip;
  }

  /**
   * Internal method to validate the local part of the email address
   *
   * @return boolean
   */
  private function _validateLocalPart()
  {
    // First try to match the local part on the common dot-atom format
    $result = false;

    // Dot-atom characters are: 1*atext *("." 1*atext)
    // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
    //        "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
    $atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e';
    
    if (preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $this->_localPart))
    {
      $result = true;
    }
    else
    {
      // Try quoted string format

      // Quoted-string characters are: DQUOTE *([FWS] qtext/quoted-pair) [FWS] DQUOTE
      // qtext: Non white space controls, and the rest of the US-ASCII characters not
      //   including "\" or the quote character
      $noWsCtl = '\x01-\x08\x0b\x0c\x0e-\x1f\x7f';
      $qtext   = $noWsCtl . '\x21\x23-\x5b\x5d-\x7e';
      $ws      = '\x20\x09';
      
      if (preg_match('/^\x22([' . $ws . $qtext . '])*[$ws]?\x22$/', $this->_localPart))
      {
        $result = true;
      }
      else
      {
        $this->_error(self::DOT_ATOM);
        $this->_error(self::QUOTED_STRING);
        $this->_error(self::INVALID_LOCAL_PART);
      }
    }

    return $result;
  }

  /**
   * Internal method to validate the servers MX records
   *
   * @return boolean
   */
  private function _validateMXRecords()
  {
    $mxHosts = array();
    $result = getmxrr($this->_hostname, $mxHosts);
    
    if (!$result)
    {
      $this->_error(self::INVALID_MX_RECORD);
    }
    else if ($this->_options['deep'] && function_exists('checkdnsrr'))
    {
      $validAddress = false;
      $reserved     = true;
      
      foreach ($mxHosts as $hostname)
      {
        $res = $this->_isReserved($hostname);
        
        if (!$res)
        {
          $reserved = false;
        }

        if (!$res
            && (checkdnsrr($hostname, "A")
            || checkdnsrr($hostname, "AAAA")
            || checkdnsrr($hostname, "A6")))
        {
          $validAddress = true;
          break;
        }
      }

      if (!$validAddress)
      {
        $result = false;
        
        if ($reserved)
        {
          $this->_error(self::INVALID_SEGMENT);
        }
        else
        {
          $this->_error(self::INVALID_MX_RECORD);
        }
      }
    }

    return $result;
  }

  /**
    * Internal method to validate the hostname part of the email address
    *
    * @return boolean
    */
  private function _validateHostnamePart()
  {
    $hostname = $this->_options['hostname']->setTranslator($this->getTranslator())
                      ->isValid($this->_hostname);
    if (!$hostname)
    {
      $this->_error(self::INVALID_HOSTNAME);

      // Get messages and errors from hostnameValidator
      foreach ($this->_options['hostname']->getMessages() as $code => $message)
      {
        $this->_messages[$code] = $message;
      }

      foreach ($this->_options['hostname']->getErrors() as $error)
      {
        $this->_errors[] = $error;
      }
    }
    else if ($this->_options['mx'])
    {
      // MX check on hostname
      $hostname = $this->_validateMXRecords();
    }

    return $hostname;
  }

  public function isValid($value)
  {
    if (!is_string($value))
    {
      $this->_error(self::INVALID);
      return false;
    }

    $matches = array();
    $length  = true;
    $this->_setValue($value);

    // Split email address up and disallow '..'
    if ((strpos($value, '..') !== false) or
        (!preg_match('/^(.+)@([^@]+)$/', $value, $matches)))
    {
      $this->_error(self::INVALID_FORMAT);
      return false;
    }

    $this->_localPart = $matches[1];
    $this->_hostname  = $matches[2];

    if ((strlen($this->_localPart) > 64) || (strlen($this->_hostname) > 255))
    {
      $length = false;
      $this->_error(self::LENGTH_EXCEEDED);
    }

    // Match hostname part
    if ($this->_options['domain'])
    {
      $hostname = $this->_validateHostnamePart();
    }

    $local = $this->_validateLocalPart();

    // If both parts valid, return true
    if ($local && $length)
    {
      if (($this->_options['domain'] && $hostname) || !$this->_options['domain'])
      {
        return true;
      }
    }
    
    if ( count($this->_errors) > 0 )
    {
      $this->_errors = array(self::COMMON_ERROR);
      $this->_messages = array(self::COMMON_ERROR => self::COMMON_ERROR);
    }
    
    return false;
  }
}