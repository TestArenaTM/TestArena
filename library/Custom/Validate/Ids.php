<?php

class Custom_Validate_Ids extends Zend_Validate_Abstract
{
  const INVALID_IDS = 'invalidIds';
  
  protected $_allowZeroValue = false;
  
  public function __construct($options = array())
  {
    if (array_key_exists('allowZeroValue', $options))
    {
      $this->_allowZeroValue = $options['allowZeroValue'];
    }
  }

  protected $_messageTemplates = array(
    self::INVALID_IDS => "'%value%' are not a valid ids.",
  );

  public function isValid($value)
  {
    $this->_setValue($value);
    $ids = explode('_', $value);
    
    if ($this->_allowZeroValue)
    {
      foreach ($ids as $id)
      {
        if (!is_numeric($id) || $id < 0)
        {
          $this->_error(self::INVALID_IDS);
          return false;
        }
      }
    }
    else
    {
      foreach ($ids as $id)
      {
        if (!is_numeric($id) || $id <= 0)
        {
          $this->_error(self::INVALID_IDS);
          return false;
        }
      }
    }

    return true;
  }
}