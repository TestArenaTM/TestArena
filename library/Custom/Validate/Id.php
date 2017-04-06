<?php

class Custom_Validate_Id extends Zend_Validate_Abstract
{
  const INVALID_ID = 'invalidId';
  
  protected $_allowZeroValue = false;
  
  public function __construct($options = array())
  {
    if (array_key_exists('allowZeroValue', $options))
    {
      $this->_allowZeroValue = $options['allowZeroValue'];
    }
  }

  protected $_messageTemplates = array(
    self::INVALID_ID => "'%value%' is not a valid id.",
  );

  public function isValid($value)
  {
    $this->_setValue($value);
    
    if ($this->_allowZeroValue)
    {
      if (!is_numeric($value) || $value < 0)
      {
        $this->_error(self::INVALID_ID);
        return false;
      }
    }
    else
    {
      if (!is_numeric($value) || $value <= 0)
      {
        $this->_error(self::INVALID_ID);
        return false;
      }
    }

    return true;
  }
}