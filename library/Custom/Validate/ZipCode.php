<?php
class Custom_Validate_ZipCode extends Zend_Validate_Abstract
{
  const ZIP_CODE_INVALID_CHARACTERS = 'zipCodeInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::ZIP_CODE_INVALID_CHARACTERS => "Zip code can only be numbers and hyphen.",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[0-9]{2}[-]?[0-9]{3}$/iu', $value))
    {
      $this->_error(self::ZIP_CODE_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}