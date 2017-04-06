<?php

class Custom_Validate_Url extends Zend_Validate_Abstract
{
  const INVALID_URL = 'invalidUrl';
  const URL_NOT_EXISTS = 'urlNotExists';

  protected $_messageTemplates = array(
    self::INVALID_URL => "'%value%' is not a valid URL.",
    self::URL_NOT_EXISTS => "URL '%value%' is not exists."
  );
  
  private $_checkExists = false;
  
  public function __construct($options = array())
  {
    if (array_key_exists('checkExists', $options))
    {
      $this->_checkExists = (bool)$options['checkExists'];
    }
  }

  public function isValid($value)
  {
    $this->_setValue((string)$value);

    if (!Zend_Uri::check($value))
    {
      $this->_error(self::INVALID_URL);
      return false;
    }
    elseif ($this->_checkExists && !$this->_isExists($value))
    {
      $this->_error(self::URL_NOT_EXISTS);
      return false;
    }

    return true;
  }
  
  private function _isExists($url)
  {
    $result = @get_headers($url);
    return is_array($result) && $result[0] != 'HTTP/1.1 404 Not Found';
  }
}