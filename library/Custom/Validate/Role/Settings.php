<?php
class Custom_Validate_Role_Settings extends Zend_Validate_Abstract
{
  const ROLE_SETTINGS_EMPTY = 'roleSettingsEmpty';
  
  protected $_messageTemplates = array
  (
    self::ROLE_SETTINGS_EMPTY => 'Role settings are not set.'
  ); 
    
  public function isValid($value, $context = null)
  {
    if (array_sum($context['roleSettings']) <= 0)
    {
      $this->_error(self::ROLE_SETTINGS_EMPTY);
      return false;
    }
    
    return true;
  }
}