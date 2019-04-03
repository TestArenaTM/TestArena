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
class Project_Model_TagMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TagDbTable';
  
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
      $tag = new Application_Model_Tag();
      $list[] = $tag->setDbProperties($row);
    }
    
    return array($list, $paginator, $adapter->count());
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getAllAjax($request)->toArray();
  }
  
  public function getForPopulateByIds(array $ids)
  {
    $result = $this->_getDbTable()->getForPopulateByIds($ids);
    return $result->toArray();
  }
  
  public function getForPopulateByTask(Application_Model_Task $task)
  {
    $result = $this->_getDbTable()->getForPopulateByTask($task->getId());
    return $result->toArray();
  }
  
  public function getForPopulateByDefect(Application_Model_Defect $defect)
  {
    $result = $this->_getDbTable()->getForPopulateByDefect($defect->getId());
    return $result->toArray();
  }

  public function add(Application_Model_Tag $tag)
  {
    $data = array(
      'project_id'  => $tag->getProjectId(),
      'name'        => $tag->getName()
    );
    
    try
    {
      $this->_getDbTable()->insert($data);
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }

    return true;
  }

  public function getForEdit(Application_Model_Tag $tag)
  {
    $row = $this->_getDbTable()->getForEdit($tag->getId(), $tag->getProjectId());
    
    if (null === $row)
    {
      return false;
    }

    $row = $row->toArray();
    $tag->setDbProperties($row);
    return $tag->map($row);
  }

  public function getForView(Application_Model_Tag $tag)
  {
    $row = $this->_getDbTable()->getForView($tag->getId(), $tag->getProjectId());
    
    if (null === $row)
    {
      return false;
    }

    return $tag->setDbProperties($row->toArray());
  }
  
  public function save(Application_Model_Tag $tag)
  {
    if ($tag->getId() === null)
    {
      return false;
    }
    
    try
    {
      $data = array(
        'name'        => $tag->getName()       
      );
    
      $where = array(
        'id = ?' => $tag->getId()
      );

      $this->_getDbTable()->update($data, $where);
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return true;
  }
  
  public function delete(Application_Model_Tag $tag)
  {
    if ($tag->getId() === null)
    {
      return false;
    }

    try
    {
      $this->_getDbTable()->delete(array('id = ?' => $tag->getId()));
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return true;
  }

  public function getByTask(Application_Model_Task $task)
  {
    $rows = $this->_getDbTable()->getByTask($task->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_Tag($row);
    }
    
    return $list;
  }

  public function getByDefect(Application_Model_Defect $defect)
  {
    $rows = $this->_getDbTable()->getByDefect($defect->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_Tag($row);
    }
    
    return $list;
  }
  
  public function getByProjectAsOptions(Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getByProjectAsOptions($project->getId());
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = $row['name'];
    }
    
    return $list;
  }

  public function getForFilterByIds(array $ids)
  {
    $rows = $this->_getDbTable()->getForFilterByIds($ids);
    
    if (null === $rows)
    {
      return array();
    }
    
    return $rows->toArray();
  }
}