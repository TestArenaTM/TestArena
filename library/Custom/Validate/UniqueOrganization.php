<?php
class Custom_Validate_UniqueOrganization extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'organizationExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Organization %value% already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'user';
    $options['field'] = 'organization';
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