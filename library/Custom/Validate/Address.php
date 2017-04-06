<?php
class Custom_Validate_Address extends Zend_Validate_Abstract
{
  const ADDRESS_INVALID_CHARACTERS = 'addressInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::ADDRESS_INVALID_CHARACTERS => "Address can only be letters, numbers, hyphen, dot, space, apostrophe and char /.",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[\p{L}]{1}[-\p{L}0-9\s\\\'\.\/]*$/iu', $value))
    {
      $this->_error(self::ADDRESS_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}