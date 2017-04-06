<?php
class Custom_Validate_Users extends Custom_Validate_DbCountAbstract
{
  const USERS_INVALID_IDS = 'usersInvalidIds';
  const USER_NOT_EXISTS   = 'userNotExists';
  
  protected $_messageTemplates = array
  (
    self::USERS_INVALID_IDS => 'User ids can only be numbers.',
    self::USER_NOT_EXISTS => 'User not exists in database.'
  );
    
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'user';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $users = explode(',', $value);
    
    foreach($users as $user)
    {
      if (!is_numeric($user) || $user <= 0)
      {
        $this->_error(self::USERS_INVALID_IDS);
        return false;
      }
    }
    
    $result = $this->_countSelect($value, $users);
    
    if (count($users) != $result)
    {
      $this->_error(self::USER_NOT_EXISTS);
      return false;
    }
    
    return true;
  }
}