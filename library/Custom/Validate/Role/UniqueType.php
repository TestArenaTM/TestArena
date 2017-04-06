<?php
class Custom_Validate_Role_UniqueType extends Custom_Validate_DbAbstract
{
  const ROLENAME_EXISTS = 'roleTypeExists';
  
  protected $_messageTemplates = array
  (
    self::ROLENAME_EXISTS => 'Role type already exists in project!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'role';
    $options['field'] = 'type';
  }
  
  public function isValid($value, $context = null)
  {
    $this->_setValue($value);
    
    if ($value == Application_Model_RoleType::CUSTOM)
    {
      return true;
    }
    
    $projects = explode(',', $context['projects']);
    
    foreach($projects as $project)
    {
      if (!is_numeric($project) || $project <= 0)
      {
        return false;
      }
    }
    
    $result = $this->_uniqueRoleTypeSelect($value, $context['projects']);

    if ($result[$this->_field] == $value)
    {
      $this->_error(self::ROLENAME_EXISTS);
      return false;
    }
    
    return true;
  }
  
  protected function _uniqueRoleTypeSelect($value, $projectsIds)
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
    
    $select->where($db->quoteIdentifier('project_id', true).' IN (?)', $projectsIds); // named
    
    if ($this->_exclude !== null)
    {
      $select->where($db->quoteIdentifier('id', true).' != ?', $this->_exclude);
    }

    $this->setSelect($select);
    return $this->_query($value);
  }
}