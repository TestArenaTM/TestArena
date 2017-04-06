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
class Project_Model_VersionDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'version';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sqlDefectCnt = '(SELECT COUNT(dv.defect_id) FROM defect_version AS dv WHERE dv.version_id=v.id)';
    
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(
        'id',
        'name',
        'defectCount' => new Zend_Db_Expr($sqlDefectCnt)
      ))
      ->joinLeft(array('tv' => 'task_version'), 'tv.version_id = v.id', array('taskCount' => 'COUNT(tv.task_id)'))
      ->group('v.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request); 
    
    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(*)'));
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(
        'id',
        'name'
      ))
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    

    return $this->fetchAll($sql);
  }
  
  public function getForPopulateByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array('id', 'name'))
      ->where('v.id IN (?)', $ids)
      ->limit(count($ids));
    
    return $this->fetchAll($sql);
  }
  
  public function getForPopulateByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array('id', 'name'))
      ->join(array('tv' => 'task_version'), 'tv.version_id = v.id', array())
      ->where('tv.task_id = ?', $taskId);
    
    return $this->fetchAll($sql);
  }
  
  public function getForPopulateByDefect($defectId)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array('id', 'name'))
      ->join(array('dv' => 'defect_version'), 'dv.version_id = v.id', array())
      ->where('dv.defect_id = ?', $defectId);
    
    return $this->fetchAll($sql);
  }
  
  public function getForEdit($id)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(
        'name'
      ))
      ->where('v.id = ?', $id)
      ->limit(1);
    
    return $this->fetchRow($sql);
  }
  
  public function getForView($id)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(
        'id',
        'name'
      ))      
      ->joinLeft(array('tv' => 'task_version'), 'tv.version_id = v.id', array('taskCount' => 'COUNT(tv.task_id)'))
      ->group('v.id')
      ->where('v.id = ?', $id)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('tv' => 'task_version'), 'tv.version_id = v.id', array())
      ->where('tv.task_id = ?', $taskId)
      ->group('v.id')
      ->setIntegrityCheck(false);
 
    return $this->fetchAll($sql);
  }
  
  public function getByDefect($defectId)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('tv' => 'defect_version'), 'tv.version_id = v.id', array())
      ->where('tv.defect_id = ?', $defectId)
      ->group('v.id')
      ->setIntegrityCheck(false);
 
    return $this->fetchAll($sql);
  }
  
  public function getByProjectAsOptions($projectId)
  {
    $sql = $this->select()
      ->from(array('v' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('v.project_id = ?', $projectId)
      ->order('v.name');
    
    return $this->fetchAll($sql);
  }
}