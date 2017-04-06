<?php
class Custom_Validate_UniqueProjectResolutionName extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'projectResolutionNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Resolution name already exists in project!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'resolution';
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