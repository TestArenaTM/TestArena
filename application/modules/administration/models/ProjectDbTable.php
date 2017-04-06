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
class Administration_Model_ProjectDbTable extends Custom_Model_DbTable_Criteria_AbstractList
{
  protected $_name = 'project';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'id',
        'prefix',
        'status',
        'create_date',
        'name'
      ))
      ->group('p.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(p.id)'))
      ->setIntegrityCheck(false);
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('p.status <> ?', Application_Model_ProjectStatus::FINISHED)
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    

    return $this->fetchAll($sql);;
  }
  
  public function getById($id)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name))
      ->where('p.id = (?)', $id)
      ->limit(1);
    
    return $this->fetchRow($sql);
  }
  
  public function getForPopulateByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array('id', 'name'))
      ->where('p.id IN (?)', $ids)
      ->limit( count($ids) );
    
    return $this->fetchAll($sql);
  }
  
  public function getSqlByIds(array $ids)
  {
    return $ids;
  }
  
  public function getForEdit($id)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'status',
        'prefix',
        'name',
        'description',
        'open_status_color',
        'in_progress_status_color'
      ))
      ->where('p.id = ?', $id)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getForView($id)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'prefix',
        'status',
        'create_date',
        'name',
        'description',
        'open_status_color',
        'in_progress_status_color'
      ))
      ->where('id = ?', $id)
      ->limit(1);
    
    return $this->fetchRow($sql);
  }
  
  public function getNotFinishedAllAsOptions()
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('p.status != ?', Application_Model_ProjectStatus::FINISHED)
      ->order('p.name')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
  
  public function checkIfExists($projectId)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'id'
      ))
      ->where('p.id = (?)', $projectId)
      ->limit(1);
    
    return $this->getAdapter()->fetchOne($sql);
  }
}