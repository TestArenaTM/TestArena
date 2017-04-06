<?php
class Custom_Validate_Versions extends Custom_Validate_DbCountAbstract
{
  const VERSION_INVALID_IDS = 'versionInvalidIds';
  const VERSION_NOT_EXISTS   = 'versionNotExists';
  
  protected $_messageTemplates = array
  (
    self::VERSION_INVALID_IDS => 'Version ids can only be numbers.',
    self::VERSION_NOT_EXISTS => 'Version not exists in database.'
  );
    
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'version';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $versions = explode(',', $value);
    
    foreach($versions as $version)
    {
      if (!is_numeric($version) || $version <= 0)
      {
        $this->_error(self::VERSION_INVALID_IDS);
        return false;
      }
    }
    
    $result = $this->_countSelect($value, $versions);
    
    if (count($versions) != $result)
    {
      $this->_error(self::VERSION_NOT_EXISTS);
      return false;
    }
    
    return true;
  }
}