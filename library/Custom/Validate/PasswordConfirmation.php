<?php
class Custom_Validate_PasswordConfirmation extends Zend_Validate_Abstract
{
  const NOT_MATCH = 'passwordNotMatch';

  protected $_messageTemplates = array (
    self::NOT_MATCH => 'Password confirmation does not match'
  );
  
  private $_confirmFieldName = '';
  
  public function __construct($options = array())
  {
    if (array_key_exists('confirmFieldName', $options))
    {
      $this->_confirmFieldName = $options['confirmFieldName'];
    }
  }

  public function isValid($value, $context = null)
  {
    $this->_setValue($value);
    
    if (is_array($context))
    {
      if (isset($context[$this->_confirmFieldName])
          && ($value == $context[$this->_confirmFieldName]))
      {
        return true;
      }
    }
    elseif (is_string($context) && ($value == $context))
    {
      return true;
    }

    $this->_error(self::NOT_MATCH);
    
    return false;
  }
}