<?php
class Custom_Validate_ProjectPrefix extends Zend_Validate_Abstract
{
  const PROJECT_PREFIX_INVALID_CHARACTERS = 'projectPrefixInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::PROJECT_PREFIX_INVALID_CHARACTERS => "Project prefix can only be letters and numbers.",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    
    if (!@preg_match('/^[a-z\d]+$/iu', $value))
    {
      $this->_error(self::PROJECT_PREFIX_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}