<?php
class Custom_Validate_UniqueProjectResolutionColor extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'projectResolutionColorExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Resolution color already exists in project!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'resolution';
    $options['field'] = 'color';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $result = $this->_uniqueSelect($value);

    if (strtolower($result[$this->_field]) == strtolower($value))
    {
      $this->_error(self::ERROR_EXISTS);
      return false;
    }

    return true;
  }
}