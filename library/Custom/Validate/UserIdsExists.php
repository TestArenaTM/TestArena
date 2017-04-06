<?php
class Custom_Validate_UserIdsExists extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_NOT_EXISTS = 'userIdNotExists';
  
  protected $_messageTemplates = array (
    self::ERROR_NOT_EXISTS => 'Id %value% not exists in database!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'user';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $ids = explode('_', $value);
    
    foreach ($ids as $id)
    {
      $result = $this->_query($id);
    
      if (!$result)
      {
        $this->_error(self::ERROR_NOT_EXISTS);
        return false;
      }
    }

    return true;
  }
}