<?php

class Custom_Validate_FormSelectWrongValue extends Zend_Validate_Abstract
{
  private $_wrongValue = 0;
  
  public function __construct($options = array())
  {
    if (array_key_exists('wrongValue', $options))
    {
      $this->_wrongValue = $options['wrongValue'];
    }
  }
  
  public function isValid( $value)
  {
    $this->_setValue($value);
   
    $value = Utils_Text::unicodeTrim($value);
      
    if ($value == $this->_wrongValue)
    {
      $this->_error(Zend_Validate_NotEmpty::IS_EMPTY);
      return false;
    }
    
    return true;
  }
}