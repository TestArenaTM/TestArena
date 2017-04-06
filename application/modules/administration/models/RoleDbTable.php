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
class Administration_Model_RoleDbTable extends Custom_Model_DbTable_Criteria_AbstractList
{
  protected $_name = 'role';

  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'projectName' => new Zend_Db_Expr('p.name')
      ))
      ->joinInner(array('p' => 'project'), 'r.project_id = p.id', $this->_createAlias('project', array(
        'id',
        'name',
        'status'
      )))
      ->where('p.status != ?', Application_Model_ProjectStatus::FINISHED)
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    
   
    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(r.id)'))
      ->joinInner(array('p' => 'project'), 'r.project_id = p.id', array())
      ->where('p.status != ?', Application_Model_ProjectStatus::FINISHED);
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getSqlByIds(array $ids)
  {
    return $ids;
  }
  
  public function getById($id)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'role_settings' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT CONCAT(rs.role_action_id ))'),
        'users' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT ur.user_id )')
      ))
      ->joinInner(array('p' => 'project'), 'r.project_id = p.id', $this->_createAlias('project', array(
        'id',
        'name',
        'status'
      )))
      ->joinLeft(array('rs' => 'role_settings'), 'rs.role_id = r.id', array())
      ->joinLeft(array('ur' => 'role_user'), 'ur.role_id = r.id', array())
      ->where('r.id = ?', $id)
      ->group('r.id')
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getListByProjectId($projectId)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'users' => new Zend_Db_Expr('GROUP_CONCAT(ur.user_id )')
      ))
      ->joinLeft(array('ur' => 'role_user'), 'ur.role_id = r.id', array())
      ->where('r.project_id = ?', $projectId)
      ->group('r.id')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }

  public function getForExportByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('ro' => $this->_name), array(
        'id',
        'name'
      ))
      ->where('ro.project_id = ?', $projectId);

    return $this->fetchAll($sql);
  }
}