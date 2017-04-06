<?php
class Custom_Validate_DateEarlierToField extends Custom_Validate_DateAbstract
{
  const DATE_IS_NOT_EARIEL = 'dateIsNotEarlier';
  const DATE_IS_NOT_EARIEL_OR_EQUAL = 'dateIsNotEarlierOrEqual';

  protected $_messageTemplates = array (
    self::DATE_IS_NOT_EARIEL => 'Provided date must be earlier with the date \'%value%\'.',
    self::DATE_IS_NOT_EARIEL_OR_EQUAL => 'Provided date must be earlier or equal with the date \'%value%\'.'
  );

  public function isValid($value, $context = null)
  {
    if (!parent::isValid($value, $context))
    {
      if ($this->_inclusive)
      {
        $this->_error(self::DATE_IS_NOT_EARIEL_OR_EQUAL);
      }
      else
      {
        $this->_error(self::DATE_IS_NOT_EARIEL);
      }
      return false;
    }
    
    return true;
  }
  
  protected function _check($value1, $value2)
  {
    if (strlen($value2) == 0)
    {
      return true;
    }
    
    if ($this->_inclusive)
    {
      return strtotime($value1) <= strtotime($value2);
    }
    else
    {
      return strtotime($value1) < strtotime($value2);
    }
  }
}