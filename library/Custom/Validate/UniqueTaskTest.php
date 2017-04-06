<?php
class Custom_Validate_UniqueTaskTest extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'taskTestExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Test already exists in task!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'task_test';
    $options['field'] = 'test_id';
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