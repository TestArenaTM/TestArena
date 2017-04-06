<?php
class Custom_Validate_EmailExists extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_NOT_EXISTS = 'emailNotExists';
  
  protected $_messageTemplates = array (
    self::ERROR_NOT_EXISTS => 'E-mail %value% not exists in database!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'user';
    $options['field'] = 'email';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);

    $result = $this->_query($value);
    
    if (!$result)
    {
      $this->_error(self::ERROR_NOT_EXISTS);
      return false;
    }

    return true;
  }
}