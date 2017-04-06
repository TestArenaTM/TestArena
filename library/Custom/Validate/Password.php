<?php
class Custom_Validate_Password extends Zend_Validate_Abstract
{
  const PASSWORD_INVALID_CHARACTERS = 'passwordInvalidCharacters';
  const PASSWORD_INVALID_FORMAT     = 'passwordInvalidFormat';
  
  protected $_messageTemplates = array
  (
    self::PASSWORD_INVALID_CHARACTERS => "Password can only be numbers, small letters, capital letters and characters !@#$%^&*?.",
    self::PASSWORD_INVALID_FORMAT     => "Password must contain a digit, small and capital letter.",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[a-z\d\!\@\#\$\%\^\&\*\?]+$/i', $value))
    {
      $this->_error(self::PASSWORD_INVALID_CHARACTERS);
      return false;
    }

    if (!(@preg_match('/[a-z]+/', $value) && @preg_match('/[A-Z]+/', $value) && @preg_match('/\d+/', $value)))
    {
      $this->_error(self::PASSWORD_INVALID_FORMAT);
      return false;
    }
    
    return true;
  }
}