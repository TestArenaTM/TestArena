<?php
class Custom_Validate_UniqueTaskDefect extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'taskDefectExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Defect already exists in task!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'task_defect';
    $options['field'] = 'defect_id';
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