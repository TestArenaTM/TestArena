<?php
class Custom_Validate_UniqueEmail extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'emailExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'E-mail %value% already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'user';
    $options['field'] = 'email';
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