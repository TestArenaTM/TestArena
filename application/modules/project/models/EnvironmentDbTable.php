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
class Project_Model_EnvironmentDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'environment';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sqlDefectCnt = '(SELECT COUNT(de.defect_id) FROM defect_environment AS de WHERE de.environment_id=e.id)';
    $sqlTaskCnt = '(SELECT COUNT(*) FROM task_environment AS te INNER JOIN task AS t ON t.id=te.task_id WHERE te.environment_id=e.id)';
    
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
        'id',
        'name',
        'defectCount' => new Zend_Db_Expr($sqlDefectCnt),
        'taskCount' => new Zend_Db_Expr($sqlTaskCnt)
      ))
      ->group('e.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request); 
    
    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(*)'));
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
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
      ->from(array('e' => $this->_name), array('id', 'name'))
      ->where('e.id IN (?)', $ids)
      ->limit(count($ids));
    
    return $this->fetchAll($sql);
  }
  
  public function getForPopulateByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array('id', 'name'))
      ->join(array('te' => 'task_environment'), 'te.environment_id = e.id', array())
      ->where('te.task_id = ?', $taskId);
    
    return $this->fetchAll($sql);
  }
  
  public function getForPopulateByDefect($defectId)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array('id', 'name'))
      ->join(array('de' => 'defect_environment'), 'de.environment_id = e.id', array())
      ->where('de.defect_id = ?', $defectId);
    
    return $this->fetchAll($sql);
  }
  
  public function getForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
        'name',
        'description'
      ))
      ->where('e.id = ?', $id)
      ->where('e.project_id = ?', $projectId)
      ->limit(1);
    
    return $this->fetchRow($sql);
  }
  
  public function getForView($id, $projectId)
  {
    $sqlDefectCnt = '(SELECT COUNT(de.defect_id) FROM defect_environment AS de WHERE de.environment_id=e.id)';
    $sqlTaskCnt = '(SELECT COUNT(*) FROM task_environment AS te INNER JOIN task AS t ON t.id=te.task_id WHERE te.environment_id=e.id)';
    
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
        'id',
        'name',
        'description',
        'defectCount' => new Zend_Db_Expr($sqlDefectCnt),
        'taskCount' => new Zend_Db_Expr($sqlTaskCnt)
      ))      
      ->group('e.id')
      ->where('e.id = ?', $id)
      ->where('e.project_id = ?', $projectId)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('te' => 'task_environment'), 'te.environment_id = e.id', array())
      ->where('te.task_id = ?', $taskId)
      ->group('e.id')
      ->setIntegrityCheck(false);
 
    return $this->fetchAll($sql);
  }
  
  public function getByDefect($defectId)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('de' => 'defect_environment'), 'de.environment_id = e.id', array())
      ->where('de.defect_id = ?', $defectId)
      ->group('e.id')
      ->setIntegrityCheck(false);
 
    return $this->fetchAll($sql);
  }
  
  public function getByProjectAsOptions($projectId)
  {
    $sql = $this->select()
      ->from(array('e' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('e.project_id = ?', $projectId)
      ->order('e.name');
    
    return $this->fetchAll($sql);
  }
}