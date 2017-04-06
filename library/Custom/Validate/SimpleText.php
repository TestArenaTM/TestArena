<?php
class Custom_Validate_SimpleText extends Zend_Validate_Abstract
{
  const SIMPLE_TEXT_INVALID_CHARACTERS  = 'simpleTextInvalidCharacters';
  
  protected $_messageTemplates = array
  (
    self::SIMPLE_TEXT_INVALID_CHARACTERS  => "Text can only be numbers, letters, spaces and characters: !@#$%&*\/()[]<>?.,\"':;+-=_€",
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);

    if (!preg_match('/^[!@#$€%&*\/\(\)\[\]\<\>\?\.,"\':;+-=_\s\p{L}0-9\\\]+$/ui', $value))
    {
      $this->_error(self::SIMPLE_TEXT_INVALID_CHARACTERS);
      return false;
    }
    
    return true;
  }
}