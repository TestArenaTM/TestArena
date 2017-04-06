<?php
class Custom_Validate_Environments extends Custom_Validate_DbCountAbstract
{
  const ENVIRONMENT_INVALID_IDS = 'environmentInvalidIds';
  const ENVIRONMENT_NOT_EXISTS   = 'environmentNotExists';
  
  protected $_messageTemplates = array
  (
    self::ENVIRONMENT_INVALID_IDS => 'Environment ids can only be numbers.',
    self::ENVIRONMENT_NOT_EXISTS => 'Environment not exists in database.'
  );
    
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'environment';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $environments = explode(',', $value);
    
    foreach($environments as $environment)
    {
      if (!is_numeric($environment) || $environment <= 0)
      {
        $this->_error(self::ENVIRONMENT_INVALID_IDS);
        return false;
      }
    }
    
    $result = $this->_countSelect($value, $environments);
    
    if (count($environments) != $result)
    {
      $this->_error(self::ENVIRONMENT_NOT_EXISTS);
      return false;
    }
    
    return true;
  }
}