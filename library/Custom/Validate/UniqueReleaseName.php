<?php
class Custom_Validate_UniqueReleaseName extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'releaseNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Relase name already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'release';
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