<?php
class Custom_Validate_DateLaterToField extends Custom_Validate_DateAbstract
{
  const DATE_IS_NOT_LATER = 'dateIsNotLater';
  const DATE_IS_NOT_LATER_OR_EQUAL = 'dateIsNotLaterOrEqual';

  protected $_messageTemplates = array (
    self::DATE_IS_NOT_LATER => 'Provided date must be later with the date \'%value%\'.',
    self::DATE_IS_NOT_LATER_OR_EQUAL => 'Provided date must be later or equal with the date \'%value%\'.'
  );

  public function isValid($value, $context = null)
  {
    if (!parent::isValid($value, $context))
    {
      if ($this->_inclusive)
      {
        $this->_error(self::DATE_IS_NOT_LATER_OR_EQUAL);
      }
      else
      {
        $this->_error(self::DATE_IS_NOT_LATER);
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
      return strtotime($value1) >= strtotime($value2);
    }
    else
    {
      return strtotime($value1) > strtotime($value2);
    }
  }
}