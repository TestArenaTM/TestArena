<?php
class Custom_Validate_UniqueProjectName extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'projectNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Project name already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'project';
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