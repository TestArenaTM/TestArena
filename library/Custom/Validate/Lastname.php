<?php
class Custom_Validate_Lastname extends Zend_Validate_Abstract
{
  const LASTNAME_INVALID_CHARACTERS = 'lastnameInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::LASTNAME_INVALID_CHARACTERS => "Lastname can only be letters, space, hyphen and apostrophe.",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[\p{L}]{1}[-\p{L}\s\\\']*$/iu', $value))
    {
      $this->_error(self::LASTNAME_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}