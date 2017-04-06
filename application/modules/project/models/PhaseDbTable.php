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
class Project_Model_PhaseDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'phase';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('ph' => $this->_name), array(
        'id',
        'start_date',
        'end_date',
        'name',
        'description'
      ))
      ->join(array('r' => 'release'), 'r.id = ph.release_id', $this->_createAlias('release', array(
        'id',
        'name',
        'start_date',
        'end_date'
      )))
      ->joinLeft(array('t' => 'task'), 't.phase_id = ph.id', array('taskCount' => 'COUNT(t.id)'))
      ->group('ph.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);
    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('ph' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(*)'))
      ->join(array('r' => 'release'), 'ph.release_id=r.id', array())
      ->where('ph.name IS NOT NULL');
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getForEdit($id)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'name',
        'start_date',
        'end_date',
        'description'
      ))
      ->join(array('r' => 'release'), 'r.id=p.release_id', array(
        'releaseId'         => 'id',
        'releaseName'       => 'name',
        'releaseStartDate'  => 'start_date',
        'releaseEndDate'    => 'end_date'
      ))
      ->where('p.id = ?', $id)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getForView($id)
  {
    $sql = $this->select()
      ->from(array('ph' => $this->_name), array(
        'id',
        'name',
        'start_date',
        'end_date',
        'description'
      ))
      ->join(array('r' => 'release'), 'r.id = ph.release_id', $this->_createAlias('release', array(
        'id',
        'name',
        'start_date',
        'end_date'
      )))
      ->joinLeft(array('t' => 'task'), 't.phase_id = ph.id', array('taskCount' => 'COUNT(t.id)'))
      ->where('ph.id = ?', $id)
      ->group('ph.id')
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getForTask($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('p' => $this->_name), array(
        'name',
        'start_date',
        'end_date',
        'description'
      ))
      ->join(array('r' => 'release'), 'r.id=p.release_id', $this->_createAlias('release', array(
          'id',
          'name'
        ))
      )
      ->where('p.id = ?', $id)
      ->where('r.project_id = ?', $projectId)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getForListByProjectIdReleaseId($projectId, $releaseId)
  {
    $sql = $this->select()
      ->from(array('ph' => $this->_name), array(
        'id',
        'name'
      ))
      ->join(array('r' => 'release'), 'r.id = ph.release_id AND '.$this->_db->quoteInto('r.id = ?', $releaseId).' AND '.$this->_db->quoteInto('r.project_id = ?', $projectId), array())
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $this->_setRequest($request);
    
    $sql = $this->select()
      ->from(array('ph' => $this->_name), array(
        'id',
        'name',
        'startDate' => 'start_date',
        'endDate' => 'end_date'
      ))
      ->join(array('r' => 'release'), 'r.id = ph.release_id', array())
      ->order('ph.name')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);      
    return $this->fetchAll($sql);
  }
  
  public function getForForwardAjax(Zend_Controller_Request_Abstract $request)
  {
    $this->_setRequest($request);
    
    $sql[0] = $this->select()
      ->from(array('ph' => $this->_name), array(
        'id',
        'name',
        'startDate' => 'start_date',
        'endDate' => 'end_date'
      ))
      ->join(array('r' => 'release'), 'r.id = ph.release_id', array())
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql[0], $request); 
    
    $sql[1] = $this->select()
      ->from(array('ph' => $this->_name), array(
        'id',
        'name',
        'startDate' => 'start_date',
        'endDate' => 'end_date'
      ))
      ->join(array('r' => 'release'), 'r.id = ph.release_id', array())
      ->where('ph.end_date >= ?', date('Y-m-d'))
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql[1], $request);      
    return $this->fetchAll($this->union($sql)->order('name'));
  }
  
  public function getByReleaseIdAsOptions($releaseId)
  {
    $sql = $this->select()
      ->from(array('ph' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('ph.release_id = ?', $releaseId)
      ->order('ph.name')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
}