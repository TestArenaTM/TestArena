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
class Project_Model_TagDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'tag';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sqlDefectCnt = '(SELECT COUNT(dt.defect_id) FROM defect_tag AS dt WHERE dt.tag_id=t.id)';
    $sqlTaskCnt = '(SELECT COUNT(*) FROM task_tag AS tt INNER JOIN task AS ta ON ta.id=tt.task_id WHERE tt.tag_id=t.id)';
    
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name',
        'defectCount' => new Zend_Db_Expr($sqlDefectCnt),
        'taskCount' => new Zend_Db_Expr($sqlTaskCnt)
      ))
      ->group('t.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request); 

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(*)'));
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
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
      ->from(array('t' => $this->_name), array('id', 'name'))
      ->where('t.id IN (?)', $ids)
      ->limit(count($ids));
    
    return $this->fetchAll($sql);
  }
  
  public function getForPopulateByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array('id', 'name'))
      ->join(array('tt' => 'task_tag'), 'tt.tag_id = t.id', array())
      ->where('tt.task_id = ?', $taskId);
    
    return $this->fetchAll($sql);
  }
  
  public function getForPopulateByDefect($defectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array('id', 'name'))
      ->join(array('dt' => 'defect_tag'), 'dt.tag_id = t.id', array())
      ->where('dt.defect_id = ?', $defectId);
    
    return $this->fetchAll($sql);
  }
  
  public function getForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'name'
      ))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)

      ->limit(1);
    
    return $this->fetchRow($sql);
  }
  
  public function getForView($id, $projectId)
  {
    $sqlDefectCnt = '(SELECT COUNT(dt.defect_id) FROM defect_tag AS dt WHERE dt.tag_id=t.id)';
    $sqlTaskCnt = '(SELECT COUNT(*) FROM task_tag AS tt INNER JOIN task AS ta ON ta.id=tt.task_id WHERE tt.tag_id=t.id)';
    
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name',
        'defectCount' => new Zend_Db_Expr($sqlDefectCnt),
        'taskCount' => new Zend_Db_Expr($sqlTaskCnt)
      ))
      ->group('t.id')
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)

      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('tt' => 'task_tag'), 'tt.tag_id = t.id', array())
      ->where('tt.task_id = ?', $taskId)
      ->group('t.id')
      ->setIntegrityCheck(false);
 
    return $this->fetchAll($sql);
  }
  
  public function getByDefect($defectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('dt' => 'defect_tag'), 'dt.tag_id = t.id', array())
      ->where('dt.defect_id = ?', $defectId)
      ->group('t.id')
      ->setIntegrityCheck(false);
 
    return $this->fetchAll($sql);
  }
  
  public function getByProjectAsOptions($projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('t.project_id = ?', $projectId)
      ->order('t.name');
    
    return $this->fetchAll($sql);
  }
  
  public function getForFilterByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('t.id IN(?)', $ids)
      ->group('t.id')
      ->setIntegrityCheck(false);
 
    return $this->fetchAll($sql);
  }
}