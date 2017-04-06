<?php
class Custom_Validate_Firstname extends Zend_Validate_Abstract
{
  const FIRSTNAME_INVALID_CHARACTERS = 'firstnameInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::FIRSTNAME_INVALID_CHARACTERS => "Firstname can only be letters, hyphen, spaces and apostrophe.",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[\p{L}]{1}[-\p{L}\s\\\']*$/iu', $value))
    {
      $this->_error(self::FIRSTNAME_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}