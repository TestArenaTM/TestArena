<?php

require_once 'Zend/Validate/Abstract.php';

class Custom_Validate_Tag extends Custom_ValidateRegexAbstract
{
  const WRONG_TAG = 'wrongTag';

  protected $_messageTemplates = array (
    self::WRONG_TAG => 'Tag %value% is incorrect!'
  );
  
  public function isValid( $value)
  {
    $this->_setValue($value);
   
    $tag = Utils_Text::unicodeTrim($value);
      
    if (!preg_match('/^[-'.$this->_getLanguagePattern().'0-9\s]{2,20}$/u', $tag))
    {
      $this->_error(self::WRONG_TAG);
      return false;
    }
    
    return true;
  }
}