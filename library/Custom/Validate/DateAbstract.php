<?php
abstract class Custom_Validate_DateAbstract extends Zend_Validate_Abstract
{ 
  protected $_fieldName = '';
  protected $_inclusive = false;
  
  public function __construct($options = array())
  {
    if (array_key_exists('fieldName', $options))
    {
      $this->_fieldName = $options['fieldName'];
    }
    
    if (array_key_exists('inclusive', $options))
    {
      $this->_inclusive = $options['inclusive'];
    }
  }

  public function isValid($value, $context = null)
  {
    $this->_setValue($value);
    
    if (is_array($context))
    {
      $context = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($context)), true);
    
      if (isset($context[$this->_fieldName]) && $this->_check($value, $context[$this->_fieldName]))
      {
        return true;
      }
    }
    elseif (is_string($context) && $this->_check($value, $context))
    {
      return true;
    }
    
    return false;
  }
  
  abstract protected function _check($value1, $value2);
}