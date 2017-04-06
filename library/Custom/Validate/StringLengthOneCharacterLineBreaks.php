<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: StringLength.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Custom_Validate_StringLengthOneCharacterLineBreaks extends Zend_Validate_StringLength
{
  private $_stripHtmlTags = false;
  
  public function __construct($options = array())
  {
    if ($options instanceof Zend_Config)
    {
      $options = $options->toArray();
    }
    else if (!is_array($options))
    {
      $options     = func_get_args();
      $temp['min'] = array_shift($options);
      if (!empty($options))
      {
        $temp['max'] = array_shift($options);
      }

      if (!empty($options))
      {
        $temp['encoding'] = array_shift($options);
      }
      
      if (!empty($options))
      {
        $temp['stripHtmlTags'] = array_shift($options);
      }

      $options = $temp;
    }
    
    if (array_key_exists('stripHtmlTags', $options) && true === $options['stripHtmlTags'])
    {
      $this->_stripHtmlTags = true;
    }
    
    parent::__construct($options);
  }
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is not null).
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }
        
        if ($this->_stripHtmlTags)
        {
          $value = strip_tags($value);
        }
        
        $this->_setValue($value);
        $value = str_replace("\r\n", "\n", $value);
        $value = str_replace("\r", "\n", $value);
        
        if ($this->_encoding !== null) {
            $length = iconv_strlen($value, $this->_encoding);
        } else {
            $length = iconv_strlen($value);
        }

        if ($length < $this->_min) {
            $this->_error(self::TOO_SHORT);
        }

        if (null !== $this->_max && $this->_max < $length) {
            $this->_error(self::TOO_LONG);
        }
        
        if (count($this->_messages)) {
            return false;
        } else {
            return true;
        }
    }
}
