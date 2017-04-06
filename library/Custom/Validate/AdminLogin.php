<?php

require_once 'Zend/Validate/Db/Abstract.php';

class Custom_Validate_AdminLogin extends Zend_Validate_Abstract
{
  const NICK_INVALID_CHARACTERS = 'adminInvalidCharacters';
  const INTERNAL_ERROR          = 'regexErrorous';
  
  protected $_messageTemplates = array
  (
    self::NICK_INVALID_CHARACTERS => "Admin login '%value%' can only be numbers, letters, hyphen and underscore",
    self::INTERNAL_ERROR  => "There was an internal error while validating nick. Try again."
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);

    $status = @preg_match('/^[a-zA-Z]{1}[-_a-zA-Z0-9]*$/i', $value);
    
    if (false === $status)
    {
      $this->_error(self::INTERNAL_ERROR);
      return false;
    }

    if (!$status)
    {
      $this->_error(self::NICK_INVALID_CHARACTERS);
      return false;
    }
    
    $stringLength = new Zend_Validate_StringLength( array('min' => 3, 'max' => 20) );
    
    if ( !$stringLength->isValid($value) )
    {
      $stringLengthErrors = $stringLength->getErrors();
      $this->_error($stringLengthErrors[0]);
      return false;
    }
    
    return true;
  }
}