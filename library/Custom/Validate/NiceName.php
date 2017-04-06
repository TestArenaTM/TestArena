<?php
class Custom_Validate_NiceName extends Zend_Validate_Abstract
{
  const NICENAME_INVALID_CHARACTERS = 'niceNameInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::NICENAME_INVALID_CHARACTERS => "Nice name can only be letters, numbers and chars: -_",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[a-z]{1}[-_a-z0-9]*$/i', $value))
    {
      $this->_error(self::NICENAME_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}