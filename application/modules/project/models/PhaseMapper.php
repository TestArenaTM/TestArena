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
class Project_Model_PhaseMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_PhaseDbTable';
  
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
      $phase = new Application_Model_Phase();
      $list[] = $phase->setDbProperties($row);
    }
    
    return array($list, $paginator);
  }

  public function add(Application_Model_Phase $phase)
  {
    $db = $this->_getDbTable();
    $data = array(
      'release_id'  => $phase->getRelease()->getId(),
      'name'        => $phase->getName(),
      'start_date'  => $phase->getStartDate(),
      'end_date'    => $phase->getEndDate(),
      'description' => $phase->getDescription()
    );

    return $db->insert($data);
  }

  public function getForEdit(Application_Model_Phase $phase)
  {
    $row = $this->_getDbTable()->getForEdit($phase->getId());
    
    if (null === $row)
    {
      return false;
    }

    $row = $row->toArray();
    $phase->setDbProperties($row);
    return $phase->map($row);
  }

  public function getForView(Application_Model_Phase $phase)
  {
    $row = $this->_getDbTable()->getForView($phase->getId());
    
    if (null === $row)
    {
      return false;
    }

    $phase->setDbProperties($row->toArray());
    $phase->setExtraData('jsStartDate', $this->_transformToJSDate($phase->getStartDate().' 00:00:00'));
    $phase->setExtraData('jsEndDate', $this->_transformToJSDate($phase->getEndDate().' 23:59:59'));
    return $phase;
  }
  
  public function getForListByProjectRelease(Application_Model_Project $project, Application_Model_Release $release, $returnRowData = false)
  {
    $rows = $this->_getDbTable()->getForListByProjectIdReleaseId($project->getId(), $release->getId());
    
    if (empty($rows))
    {
      return false;
    }
    
    if ($returnRowData)
    {
      return $rows->toArray();
    }
    
    $phases = array();
    
    foreach($rows->toArray() as $row)
    {
      $phase = new Application_Model_Phase();
      
      $phases[] = $phase->setDbProperties($row);
    }

    return $phases;
  }
  
  public function save(Application_Model_Phase $phase)
  {
    if ($phase->getId() === null)
    {
      return false;
    }
    
    try
    {
      $data = array(
        'release_id'  => $phase->getReleaseId(),
        'name'        => $phase->getName(),
        'start_date'  => $phase->getStartDate(),
        'end_date'    => $phase->getEndDate(),
        'description' => $phase->getDescription()        
      );
      
      $where = array(
        'id = ?' => $phase->getId(), 
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
  
  public function delete(Application_Model_Phase $phase)
  {
    if ($phase->getId() === null)
    {
      return false;
    }
    
    $where = array(
      'id = ?' => $phase->getId()
    );
    
    return $this->_getDbTable()->delete($where) == 1;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getAllAjax($request)->toArray();
  }
  
  public function getForForwardAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getForForwardAjax($request)->toArray();
  }
  
  public function deleteByRelease(Application_Model_Release $release)
  {
    if ($release->getId() === null)
    {
      return false;
    }
    
    $where = array(
      'release_id = ?'  => $release->getId()
    );
    
    return $this->_getDbTable()->delete($where) > 0;
  }
  
  public function getForTask(Application_Model_Phase $phase, Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getForTask($phase->getId(), $project->getId());
    
    if (null === $row)
    {
      return false;
    }

    $row = $row->toArray();
    $phase->setDbProperties($row);
    return $phase->map($row);
  }
  
  public function getByReleaseAsOptions(Application_Model_Release $release)
  {
    $rows = $this->_getDbTable()->getByReleaseIdAsOptions($release->getId());
    
    $list = array();
    
    foreach($rows->toArray() as $row)
    {
      $list[$row['id']] = $row['name'];
    }
    
    return $list;
  }
  
  private function _transformToJSDate($date)
  {
    if (is_string($date))
    {
      $date = strtotime($date);
    }
    
    $buf = getdate($date);
    $buf['mon']--;
    return 'new Date('.$buf['year'].','.$buf['mon'].','.$buf['mday'].','.$buf['hours'].','.$buf['minutes'].','.$buf['seconds'].')';
  }
}