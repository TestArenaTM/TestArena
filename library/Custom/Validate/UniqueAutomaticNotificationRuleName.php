<?php
class Custom_Validate_UniqueAutomaticNotificationRuleName extends Custom_Validate_UniqueNotificationRuleName
{
  const ERROR_EXISTS = 'automaticNotificationRuleNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Automatic notification rule name already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'automatic_notification_rule';
    $options['field'] = 'name';
  }
}