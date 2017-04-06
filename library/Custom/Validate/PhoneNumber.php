<?php
class Custom_Validate_PhoneNumber extends Zend_Validate_Abstract
{
  const INVALID_CHARACTERS  = 'phoneNumberInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::INVALID_CHARACTERS  => "Phone number can only be numbers, spaces and characters: +-()",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);

    if (!preg_match('/^[-\+\s\d\(\)]{7,20}$/ui', $value))
    {
      $this->_error(self::INVALID_CHARACTERS);
      return false;
    }

    return true;
  }
}