<?php
abstract class Custom_Validate_DbUniqueAbstract extends Custom_Validate_DbAbstract
{
  protected $_criteria = array();
  protected $_excludeName = 'id';
  
  public function __construct($options = array())
  {
    if (array_key_exists('criteria', $options) && is_array($options['criteria']))
    {
      $this->_criteria = $options['criteria'];
    }

    parent::__construct($options);
  }
  
  protected function _uniqueSelect($value)
  {
    $db = $this->getAdapter();
    $select = new Zend_Db_Select($db);
    $select->from($this->_table, array($this->_field), $this->_schema);
    
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
      if ($v === null)
      {
        $select->where($db->quoteIdentifier($k, true).' IS NULL');
      }
      else
      {
        $select->where($db->quoteIdentifier($k, true).' = ?', $v);
      }
    }

    $this->setSelect($select);
    return $this->_query($value);
  }
}