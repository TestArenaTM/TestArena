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
class Administration_Model_UserMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_UserDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setCurrentPageNumber($request->getParam('page', 1));
    $resultCountPerPage = (int)$request->getParam('resultCountPerPage');
    $paginator->setItemCountPerPage($resultCountPerPage > 0 ? $resultCountPerPage : 10);
    
    $list = array();
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      $user = new Application_Model_User();
      $list[] = $user->setDbProperties($row);
    }
    
    return array($list, $paginator);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $request->setParam('status', Application_Model_UserStatus::ACTIVE);
    return $this->_getDbTable()->getAllAjax($request)->toArray();
  }
  
  public function add(Application_Model_User $user)
  {
    //var_dump($user->getEmail());exit;
    $data = array(
      'email'          => $user->getEmail(),
      'status'        => $user->getStatusId(),
      'create_date'   => date('Y-m-d H:i:s'),
      'token'         => $user->generateToken(),
      'firstname'     => $user->getFirstname(),
      'lastname'      => $user->getLastname(),
      'organization'  => $user->getOrganization(),
      'department'    => $user->getDepartment(),
      'phone_number'  => $user->getPhoneNumber(),
      'administrator' => $user->getAdministrator()
    );

    return $this->_getDbTable()->insert($data);
  }
  
  public function deleteByToken(Application_Model_User $user)
  {
    $this->_getDbTable()->delete(array('token = ?' => $user->getToken()));
  }
  
  /**
   * @param Application_Model_User $user
   * @return array
   */
  public function getForEdit(Application_Model_User $user)
  {
    $row = $this->_getDbTable()->getForEdit($user->getId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $user->map($row->toArray());
  }
  
  public function edit(Application_Model_User $user)
  {
    $data = array(
      'email'         => $user->getEmail(),
      'status'        => $user->getStatusId(),
      'firstname'     => $user->getFirstname(),
      'lastname'      => $user->getLastname(),
      'organization'  => $user->getOrganization(),
      'department'    => $user->getDepartment(),
      'phone_number'  => $user->getPhoneNumber(),
      'administrator' => $user->getAdministrator()
    );

    try
    {
      $this->_getDbTable()->update($data, array('id = ?' => $user->getId()));
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    return true;
  }
  
  public function activate(Application_Model_User $user)
  {
    if ($user->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_UserStatus::ACTIVE
    );
    
    $where = array(
      'status = ?' => Application_Model_UserStatus::INACTIVE,
      'id = ?'     => $user->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function deactivate(Application_Model_User $user)
  {
    if ($user->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_UserStatus::INACTIVE
    );
    
    $where = array(
      'status = ?' => Application_Model_UserStatus::ACTIVE,
      'id = ?'     => $user->getId()
    );

    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function resetPassword(Application_Model_User $user)
  {
    if ($user->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'reset_password' => 1
    );
    
    $where = array(
      'reset_password = ?' => 0,
      'id = ?'             => $user->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }  
  
  public function getForPopulateByIds(array $ids, $returnRowData = false)
  {
    $result = $this->_getDbTable()->getForPopulateByIds($ids);
    
    if ($returnRowData)
    {
      return $result->toArray();
    }
    
    return $result;
  }
  
  public function getForExportByProject(Application_Model_Project $project, $roleUsers = true, $taskAuthors = true)
  {
    $db = $this->_getDbTable();
    $list = array();
    
    try
    {
      
      if ($roleUsers)
      {
        $rows = $db->getForExportByProject($project->getId());

        if ($rows === null)
        {
          return false;
        }

        foreach ($rows->toArray() as $row)
        {
          $list[$row['id']] = $row;
        }
      }
      
      if ($taskAuthors)
      {
        $rows = $db->getTaskAuthorsForExportByProject($project->getId());

        if ($rows === null)
        {
          return false;
        }

        foreach ($rows->toArray() as $row)
        {
          $list[$row['id']] = $row;
        }
      }
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }

    return $list;
  }
  
  public function getByEmailsForImport(array $emails)
  {
    if (count($emails) == 0)
    {
      return array();
    }
    
    $rows = $this->_getDbTable()->getByEmailsForImport($emails);
    
    if ($rows === null)
    {
      return false;
    }
    
    return $rows->toArray();
  }
  
  public function addForImport(array $rows, array $emails)
  {
    $db = $this->_getDbTable();

    foreach ($this->getByEmailsForImport($emails) as $row)
    {
      if (($oldId = array_search($row['email'], $emails)) !== false)
      {
        $rows[$oldId]['id'] = $row['id']; 
      }
    }

    foreach ($rows as $i => $row)
    {
      if (!array_key_exists('id', $row))
      {
        $rows[$i]['id'] = $db->insert($row);
      }
    }
    
    return $rows;
  }
}