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
class Project_Model_ReleaseDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'release';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  { 
    $sqlDefectCnt = '(SELECT COUNT(*) FROM defect AS d WHERE d.release_id = r.id)';
    $sqlTaskCnt = '(SELECT COUNT(*) FROM task AS t WHERE t.release_id = r.id)';
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'start_date',
        'end_date',
        'name',
        'active',
        'defectCount' => new Zend_Db_Expr($sqlDefectCnt),
        'taskCount' => new Zend_Db_Expr($sqlTaskCnt)
      ))
      ->group('r.id')
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(*)'));    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getForView($id,$projectId)
  {
    $sqlDefectCnt = '(SELECT COUNT(*) FROM defect AS d WHERE d.release_id = r.id)';
    $sqlTaskCnt = '(SELECT COUNT(*) FROM task AS t WHERE t.release_id = r.id)';
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'start_date',
        'end_date',
        'description',
        'active',
        'defectCount' => new Zend_Db_Expr($sqlDefectCnt),
        'taskCount' => new Zend_Db_Expr($sqlTaskCnt)
      ))
      ->where('r.id = ?', $id)
      ->where('r.project_id = ?', $projectId)

      ->group('r.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'name',
        'start_date',
        'end_date',
        'description',
        'active'
      ))
      ->where('r.id = ?', $id)
      ->where('r.project_id = ?', $projectId)

      ->limit(1);
    
    return $this->fetchRow($sql);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $this->_setRequest($request);
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'startDate' => 'start_date',
        'endDate' => 'end_date'
      ))
      ->order('r.name')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);      
    return $this->fetchAll($sql);
  }
  
  public function getForForwardAjax(Zend_Controller_Request_Abstract $request)
  {
    $this->_setRequest($request);
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'startDate' => 'start_date',
        'endDate' => 'end_date'
      ))
      //->where('r.end_date >= ?', date('Y-m-d'))
      ->order('r.name');
    
    $this->_setWhereCriteria($sql, $request);

    return $this->fetchAll($sql);
  }
  
  public function getByProjectIdAsOptions($projectId)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('r.project_id = ?', $projectId)
      ->order('r.name')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
  
  public function getForFilterAsOptions($projectId)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('r.project_id = ?', $projectId)
      ->order('r.name')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
  
  public function getForTask($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'name',
        'start_date',
        'end_date'
      ))
      ->where('r.id = ?', $id)
      ->where('r.project_id = ?', $projectId)
      ->limit(1);
    
    return $this->fetchRow($sql);
  }
  
  public function getActive($projectId)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'start_date',
        'end_date'
      ))
      ->where('r.project_id = ?', $projectId)
      ->where('r.active = 1')
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getBasicById($id)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'start_date',
        'end_date'
      ))
      ->where('r.id = ?', $id)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
}