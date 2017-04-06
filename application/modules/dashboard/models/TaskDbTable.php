<?php
/*
Copyright © 2014 TestArena 

This file is part of TestArena.

TestArena is free software; you can redistibute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distibuted in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

The full text of the GPL is in the LICENSE file.
*/
class Dashboard_Model_TaskDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'task';
  
  public function getLimitLatestNotClosedAssigned2You(Zend_Controller_Request_Abstract $request, $limit)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'modify_date',
        'due_date',
        'title',
        'description'
      ))
      ->join(array('assigner' => 'user'), 't.assigner_id = assigner.id', $this->_createAlias('assigner', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'id',
        'prefix'
      )))
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('t.status != ?', Application_Model_TaskStatus::CLOSED)
      ->where('ru.user_id = ?', $request->getParam('userId'))
      ->where('t.assignee_id = ?', $request->getParam('userId'))
      ->order('t.modify_date DESC')
      ->group('t.id')
      ->limit($limit)
      ->setIntegrityCheck(false);
    
    if ($request->getParam('projectId', false))
    {
      $sql->where('t.project_id = ?', $request->getParam('projectId'));
    }
    else
    {
      $sql->where('p.status != ?', Application_Model_ProjectStatus::FINISHED);
    }

    return $this->fetchAll($sql);
  }
  
  public function getAllAssigned2YouCntByStatus(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'status'
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', array())
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('ru.user_id = ?', $request->getParam('userId'))
      ->where('t.assignee_id = ?', $request->getParam('userId'))
      ->group('t.id')
      ->setIntegrityCheck(false);
    
    if ($request->getParam('projectId', false))
    {
      $sql->where('t.project_id = ?', $request->getParam('projectId'));
    }
    else
    {
      $sql->where('p.status != ?', Application_Model_ProjectStatus::FINISHED);
    }
    
    return $sql;
  }
  
  public function getAllCnt(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'count' => new Zend_Db_Expr('COUNT(DISTINCT t.id)')
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', array())
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->setIntegrityCheck(false);
    
    if ($request->getParam('projectId', false))
    {
      $sql->where('t.project_id = ?', $request->getParam('projectId'));
    }
    else
    {
      $sql->where('p.status != ?', Application_Model_ProjectStatus::FINISHED);
    }
      
    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getLimitOverdue(Zend_Controller_Request_Abstract $request, $limit)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'modify_date',
        'due_date',
        'title',
        'description'
      ))
      ->join(array('assigner' => 'user'), 't.assigner_id = assigner.id', $this->_createAlias('assigner', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'id',
        'prefix'
      )))
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('t.status != ?', Application_Model_TaskStatus::CLOSED)
      ->where('t.due_date < ?', date('Y-m-d H:i:s'))
      ->where('ru.user_id = ?', $request->getParam('userId'))
      ->order('t.due_date')
      ->group('t.id')
      ->limit($limit)
      ->setIntegrityCheck(false);
    
    if ($request->getParam('projectId', false))
    {
      $sql->where('t.project_id = ?', $request->getParam('projectId'));
    }
    else
    {
      $sql->where('p.status != ?', Application_Model_ProjectStatus::FINISHED);
    }
    
    return $this->fetchAll($sql);
  }
  
  public function getNumberOfOverdue(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'count' => new Zend_Db_Expr('COUNT(DISTINCT t.id)')
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', array())
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('t.status != ?', Application_Model_TaskStatus::CLOSED)
      ->where('t.due_date < ?', date('Y-m-d H:i:s'))
      ->where('ru.user_id = ?', $request->getParam('userId'))
      ->setIntegrityCheck(false);
    
    if ($request->getParam('projectId', false))
    {
      $sql->where('t.project_id = ?', $request->getParam('projectId'));
    }
    else
    {
      $sql->where('p.status != ?', Application_Model_ProjectStatus::FINISHED);
    }
      
    return $this->getAdapter()->fetchOne($sql);
  }
}