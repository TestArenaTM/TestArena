<?php
class Custom_Validate_UniqueTestName extends Custom_Validate_DbAbstract
{
  const ERROR_EXISTS = 'testNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Test name already exists!'
  );
  protected $_criteria = array();
  
  public function __construct($options = array())
  {//print_r($options);echo 'a';
    if (array_key_exists('criteria', $options) && is_array($options['criteria']))
    {
      $this->_criteria = $options['criteria'];
    }

    parent::__construct($options);
  }
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'test';
    $options['field'] = 'name';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $result = $this->_uniqueSelect($value);

    if (mb_strtolower($result[$this->_field]) == mb_strtolower($value))
    {
      $this->_error(self::ERROR_EXISTS);
      return false;
    }

    return true;
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
      $select->where($db->quoteIdentifier('family_id', true).' != ?', $this->_exclude);
    }
    
    $select->where('current_version = 1');
    $select->where('status != ?', Application_Model_TestStatus::DELETED);
    
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