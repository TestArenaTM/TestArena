<?php
/*
Copyright © 2014 TestArena

This file is part of TestArena.

TestArena is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

The full text of the GPL is in the LICENSE file.
*/
class Administration_Model_RoleUserMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_RoleUserDbTable';
  
  public function saveAssignment(Application_Model_Role $role)
  {
    $db      = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $data    = array();
    $users   = $role->getUsers();

    $this->deleteAssignment($role);
    
    if (count($users) > 0)
    {
      
      $values  = implode(',', array_fill(0, count($users),'(?, ?)'));
      
      foreach($users as $user)
      {
        $data[] = $user->getId();
        $data[] = $role->getId();
      }
      
      $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (user_id, role_id) VALUES '.$values);
      return $statement->execute($data);
    }
    
    return true;
  }
  
  public function deleteAssignment(Application_Model_Role $role)
  {
    return $this->_getDbTable()->delete(array('role_id = ?' => $role->getId()));
  }
  
  public function getForExportByProject(Application_Model_Project $project)
  {
    try
    {
      $rows = $this->_getDbTable()->getForExportByProject($project->getId());
    
      if ($rows === null)
      {
        return false;
      }
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return $rows->toArray();
  }
  
  public function addForImport(array $rows)
  {
    $db = $this->_getDbTable();
    
    foreach ($rows as $row)
    {
      $db->insert($row);
    }
  }
}