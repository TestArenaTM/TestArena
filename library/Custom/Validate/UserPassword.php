<?php
class Custom_Validate_UserPassword extends Custom_Validate_DbAbstract
{
  const ERROR_NOT_VALID = 'userPasswordNotValid';
  
  protected $_messageTemplates = array (
    self::ERROR_NOT_VALID => 'Password is not correct.'
  );
    
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'user';
    $options['field'] = 'password';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $result = $this->_selectPassword($value);

    if (!$result)
    {
      $this->_error(self::ERROR_NOT_VALID);
      return false;
    }

    return true;
  }
  
  private function _selectPassword($value)
  {
    $db = $this->getAdapter();
    $select = new Zend_Db_Select($db);
    $select->from($this->_table, array($this->_field), $this->_schema);
    
    if ($db->supportsParameters('named'))
    {
      $select->where($db->quoteIdentifier($this->_field, true).' = SHA1(CONCAT(MD5(CONCAT(:value, "'.Application_Model_User::SALT.'")), salt))'); // named
    } 
    else
    {
      $select->where($db->quoteIdentifier($this->_field, true).' = SHA1(CONCAT(MD5(CONCAT(?, "'.Application_Model_User::SALT.'")), salt))'); // positional
    }

    $this->setSelect($select);
    return $this->_query($value);
  }
}