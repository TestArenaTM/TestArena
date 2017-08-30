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
class Project_Model_ProjectMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_ProjectDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setCurrentPageNumber($request->getParam('page', 1));
    
    $list = array();
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      $project = new Application_Model_Project();
      $list[] = $project->setDbProperties($row);
    }
    
    return array($list, $paginator);
  }
  
  public function getForPopulateByIds(array $ids, $returnRowData = false)
  {
    $result = $this->_getDbTable()->getForPopulateByIds($ids);
    
    if ( $returnRowData )
    {
      return $result->toArray();
    }
    
    return $result;
  }
  
  public function activate(Application_Model_Project $project)
  {
    if ($project->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_ProjectStatus::ACTIVE
    );
    
    $where = array(
      'status = ?' => Application_Model_ProjectStatus::SUSPENDED,
      'id = ?'      => $project->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function suspend(Application_Model_Project $project)
  {
    if ($project->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_ProjectStatus::SUSPENDED
    );
    
    $where = array(
      'status = ?' => Application_Model_ProjectStatus::ACTIVE,
      'id = ?'     => $project->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function finish(Application_Model_Project $project)
  {
    if ($project->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_ProjectStatus::FINISHED
    );
    
    $where = array(
      'status != ?' => Application_Model_ProjectStatus::FINISHED,
      'id = ?'      => $project->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
}