<?php
class Custom_Validate_UniqueDefectTask extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'defectTaskExists';

  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Task already exists in defect!'
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