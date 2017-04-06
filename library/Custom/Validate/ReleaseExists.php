<?php
class Custom_Validate_ReleaseExists extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_NOT_EXISTS = 'releaseNotExists';
  
  protected $_messageTemplates = array (
    self::ERROR_NOT_EXISTS => 'Release not exists in database!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'release';
    $options['field'] = 'id';
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