<?php
class Custom_Validate_Name extends Zend_Validate_Abstract
{
  const INVALID_CHARACTERS = 'nameInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::INVALID_CHARACTERS => "Name can only be numbers, letters, spaces and characters: !@#$%&*\/()?.,\"':;+-=_",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!preg_match('/^[!@#$%\^&*\/\(\)\?\.,"\':;+-=_\s\p{L}0-9\\\]+$/ui', $value))
    {
      $this->_error(self::INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}