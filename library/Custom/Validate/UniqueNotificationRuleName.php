<?php
class Custom_Validate_UniqueNotificationRuleName extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'notificationRuleNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Notification rule name already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'notification_rule';
    $options['field'] = 'name';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $result = $this->_uniqueSelect($value);

    if ($result[$this->_field] == $value)
    {
      $this->_error(self::ERROR_EXISTS);
      return false;
    }

    return true;
  }
}