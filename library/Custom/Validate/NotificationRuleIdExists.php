<?php
class Custom_Validate_NotificationRuleIdExists extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_NOT_EXISTS = 'NotificationRuleIdNotExists';
  
  protected $_messageTemplates = array (
    self::ERROR_NOT_EXISTS => 'Id %value% not exists in database!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'notification_rule';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);

    $result = $this->_query($value);
    
    if (!$result)
    {
      $this->_error(self::ERROR_NOT_EXISTS);
      return false;
    }

    return true;
  }
}