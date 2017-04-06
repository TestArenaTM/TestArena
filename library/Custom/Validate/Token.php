<?php

class Custom_Validate_Token extends Zend_Validate_Abstract
{
  const INVALID_TOKEN = 'invalidToken';

  protected $_messageTemplates = array(
    self::INVALID_TOKEN => "Invalid token",
  );

  public function isValid($value)
  {
    $this->_setValue($value);

    if (!preg_match('/^[a-f\d]{32,32}$/i', $value)) {
      $this->_error(self::INVALID_TOKEN);
      return false;
    }
    
    return true;
  }
}