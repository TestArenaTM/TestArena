<?php
class Custom_Validate_UniqueProjectPrefix extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'projectPrefixExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Project prefix already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'project';
    $options['field'] = 'prefix';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $result = $this->_uniqueSelect($value);

    if (strtoupper($result[$this->_field]) == strtoupper($value))
    {
      $this->_error(self::ERROR_EXISTS);
      return false;
    }

    return true;
  }
}