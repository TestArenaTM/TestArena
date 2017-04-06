<?php
class Custom_Validate_Keywords extends Zend_Validate_Abstract
{
  const KEYWORDS_INVALID_CHARACTERS = 'keywordsInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::KEYWORDS_INVALID_CHARACTERS => "Keywords can only be letters, numbers, spaces and chars: -_'\"",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $keywords = explode(',', $value);
    
    foreach ($keywords as $keyword)
    {
      if (!@preg_match('/^[-_\p{L}0-9\s\'\"]*$/iu', $keyword))
      {
        $this->_error(self::KEYWORDS_INVALID_CHARACTERS);
        return false;
      }
    }
    
    return true;
  }
}