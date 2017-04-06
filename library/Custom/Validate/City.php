<?php
class Custom_Validate_City extends Zend_Validate_Abstract
{
  const CITY_INVALID_CHARACTERS = 'cityInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::CITY_INVALID_CHARACTERS => "City can only be letters, hyphen, space and apostrophe.",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[\p{L}]{1}[-\p{L}\s\\\']*$/iu', $value))
    {
      $this->_error(self::CITY_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}