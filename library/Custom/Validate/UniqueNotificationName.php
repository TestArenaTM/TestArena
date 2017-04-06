<?php
class Custom_Validate_UniqueNotificationName extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'notificationNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Notification subject already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'notification';
    $options['field'] = 'subject';
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