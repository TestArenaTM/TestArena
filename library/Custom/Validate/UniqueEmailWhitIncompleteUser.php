<?php
class Custom_Validate_UniqueEmailWhitIncompleteUser extends Custom_Validate_UniqueEmail
{
  protected function _uniqueSelect($value)
  {
    $db = $this->getAdapter();
    $select = new Zend_Db_Select($db);
    $select->from($this->_table, array($this->_field), $this->_schema);
    $select->where($db->quoteIdentifier('status', true).' != ?', Application_Model_UserStatus::INCOMPLETE);
    
    if ($db->supportsParameters('named'))
    {
      $select->where($db->quoteIdentifier($this->_field, true).' = :value'); // named
    } 
    else
    {
      $select->where($db->quoteIdentifier($this->_field, true).' = ?'); // positional
    }

    if ($this->_exclude !== null)
    {
      $select->where($db->quoteIdentifier($this->_excludeName, true).' != ?', $this->_exclude);
    }

    foreach ($this->_criteria as $k => $v)
    {
      $select->where($db->quoteIdentifier($k, true).' = ?', $v);
    }
    
    $this->setSelect($select);
    return $this->_query($value);
  }
}