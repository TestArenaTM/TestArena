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
class Administration_Model_RoleMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_RoleDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setCurrentPageNumber($request->getParam('page'));
    $resultCountPerPage = (int)$request->getParam('resultCountPerPage');
    $paginator->setItemCountPerPage($resultCountPerPage > 0 ? $resultCountPerPage : 10);
    
    $list = array();
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      $role = new Application_Model_Role();
      $list[] = $role->setDbProperties($row);
    }
    
    return array($list, $paginator);
  }
  
  public function getById(Application_Model_Role $role)
  {
    $row = $this->_getDbTable()->getById($role->getId());
    
    if (null === $row)
    {
      return false;
    }
    
    $userMapper = new Administration_Model_UserMapper();
    
    $userData = $userMapper->getForPopulateByIds(explode(',', $row->users), true);
    
    $roleData = array_merge(
      $row->toArray(),
      array('roleSettings' => explode(',',$row->role_settings)),
      array('users' => $userData)
    );
    
    $role->setDbProperties($roleData);
    $role->setExtraData('roleData', $roleData);
    
    return $role;
  }
  
  public function addRole(Application_Model_Role $role, $useTransaction = true)
  {
    $db          = $this->_getDbTable();
    $adapter     = $db->getAdapter();
    $projectsIds = explode(',', $role->getExtraData('projects'));
    
    $roleSettingMapper = new Administration_Model_RoleSettingMapper();
    $roleUserMapper    = new Administration_Model_RoleUserMapper();
    
    try
    {
      if ($useTransaction)
      {
        $adapter->beginTransaction();
      }
      
      $data = array(
        'name' => $role->getName()
      );
      
      foreach ($projectsIds as $projectId)
      {
        $data['project_id'] = $projectId;
        $roleId = $db->insert($data);
        $role->setId($roleId);
        $roleSettingMapper->addSettings($role);
        $roleUserMapper->saveAssignment($role);
      }
      
      if ($useTransaction)
      {
        return $adapter->commit();
      }
    }
    catch (Exception $e)
    {
      if ($useTransaction)
      {
        $adapter->rollback();
      }
      
      throw $e;
    }
  }
  
  public function editRole(Application_Model_Role $role)
  {
    $db                = $this->_getDbTable();
    $adapter           = $db->getAdapter();
    $roleSettingMapper = new Administration_Model_RoleSettingMapper();
    $roleUserMapper    = new Administration_Model_RoleUserMapper();
    
    try
    {
      $adapter->beginTransaction();
      
      $data = array(
        'name' => $role->getName(),
        'project_id' => $role->getExtraData('projects')
      );
      
      $db->update($data, array('id = ?' => $role->getId()));
      
      $roleSettingMapper->editSettings($role);
      $roleUserMapper->saveAssignment($role);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollback();
      throw $e;
    }
  }
  
  public function deleteRole(Application_Model_Role $role)
  {
    $db                = $this->_getDbTable();
    $adapter           = $db->getAdapter();
    $roleSettingMapper = new Administration_Model_RoleSettingMapper();
    $roleUserMapper    = new Administration_Model_RoleUserMapper();
    
    try
    {
      $adapter->beginTransaction();
      
      $roleSettingMapper->deleteSettings($role);
      $roleUserMapper->deleteAssignment($role);
      
      $db->delete(array('id = ?' => $role->getId()));
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollback();
      throw $e;
    }
  }
  /**
   * Metoda bez transakcji.
   * @param Application_Model_Role $role
   */
  public function addDefaultRole(Application_Model_Role $role)
  {
    $db                = $this->_getDbTable();
    $roleSettingMapper = new Administration_Model_RoleSettingMapper();
    
    $data = array(
      'project_id' => $role->getProject()->getId(),
      'type' => $role->getTypeId(),
      'name' => $role->getName()
    );

    $id = $db->insert($data);
    $role->setId($id);
    $roleSettingMapper->addSettings($role);
  }
  
  public function getListByProjectId(Application_Model_Project $project)
  {
    $list = array();
    $rows = $this->_getDbTable()->getListByProjectId($project->getId());
    
    if (count($rows) > 0)
    {
      $userMapper = new Administration_Model_UserMapper();

      foreach ($rows as $row)
      {
        $userData = $userMapper->getForPopulateByIds(explode(',', $row->users), true);

        $roleData = array_merge(
          $row->toArray(),
          array('users' => $userData)
        );

        $role = new Application_Model_Role();
        $role->setDbProperties($roleData);
        $list[] = $role;
      }
    }

    return $list;
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
    
    foreach ($rows as $i => $row)
    {
      $rows[$i]['id'] = $db->insert($row);
    }
    
    return $rows;
  }
}