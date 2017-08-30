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
  
  public function getLimitLastNotClosedAssignedToMe($userId, $projectId, $releaseId, $limit)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'modify_date',
        'due_date',
        'title'
      ))
      ->join(array('assigner' => 'user'), 't.assigner_id = assigner.id', $this->_createAlias('assigner', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->setIntegrityCheck(false);

    $this->_prepareSql($sql, $projectId, $releaseId);
    
    $sql
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('ru.user_id = ?', $userId)
      ->where('t.assignee_id = ?', $userId)
      ->order('t.modify_date DESC')
      ->group('t.id')
      ->limit($limit);

    return $this->fetchAll($sql);
  }
  
  public function countNotClosedAssignedToMe($userId, $projectId, $releaseId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'count' => new Zend_Db_Expr('COUNT(DISTINCT t.id)')
      ))
      ->setIntegrityCheck(false);

    $this->_prepareSql($sql, $projectId, $releaseId);
    
    $sql
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('ru.user_id = ?', $userId)
      ->where('t.assignee_id = ?', $userId);

    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getSqlAssignedToMe($userId, $projectId, $releaseId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'status'
      ))
      ->setIntegrityCheck(false);

    $this->_prepareSql($sql, $projectId, $releaseId);
    
    $sql
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('ru.user_id = ?', $userId)
      ->where('t.assignee_id = ?', $userId)
      ->group('t.id');
    
    return $sql;
  }
  
  public function countAll($projectId, $releaseId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'count' => new Zend_Db_Expr('COUNT(DISTINCT t.id)')
      ))
      ->setIntegrityCheck(false);

    $this->_prepareSql($sql, $projectId, $releaseId);
      
    $sql
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array());
    
    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getLimitOverdueAssignedToMe($userId, $projectId, $releaseId, $limit)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'modify_date',
        'due_date',
        'title'
      ))
      ->join(array('assigner' => 'user'), 't.assigner_id = assigner.id', $this->_createAlias('assigner', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->setIntegrityCheck(false);

    $this->_prepareSql($sql, $projectId, $releaseId);
    
    $sql
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('t.status != ?', Application_Model_TaskStatus::CLOSED)
      ->where('t.due_date < ?', date('Y-m-d H:i:s'))
      ->where('ru.user_id = ?', $userId)
      ->where('t.assignee_id = ?', $userId)
      ->order('t.due_date')
      ->group('t.id')
      ->limit($limit);
    
    return $this->fetchAll($sql);
  }
  
  public function countOverdueAssignedToMe($userId, $projectId, $releaseId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'count' => new Zend_Db_Expr('COUNT(DISTINCT t.id)')
      ))
      ->setIntegrityCheck(false);

    $this->_prepareSql($sql, $projectId, $releaseId);
    
    $sql
      ->join(array('r' => 'role'), 'r.project_id = p.id', array())
      ->join(array('ru' => 'role_user'), 'ru.role_id = r.id', array())
      ->where('t.status != ?', Application_Model_TaskStatus::CLOSED)
      ->where('t.due_date < ?', date('Y-m-d H:i:s'))
      ->where('ru.user_id = ?', $userId)
      ->where('t.assignee_id = ?', $userId);
      
    return $this->getAdapter()->fetchOne($sql);
  }
  
  private function _prepareSql(Zend_Db_Select $sql, $projectId, $releaseId)
  {
    if ($releaseId > 0)
    {
      $sql
        ->join(array('re' => 'release'), 're.id = t.release_id', array())
        ->join(array('p' => 'project'), 'p.id = re.project_id', $this->_createAlias('project', array(
          'id',
          'prefix'
        )))
        ->where('t.release_id = ?', $releaseId);
    }
    else
    {
      $sql->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'id',
        'prefix'
      )));
      
      if ($projectId > 0)
      {
        $sql->where('t.project_id = ?', $projectId);
      }
      else
      {
        $sql->where('p.status != ?', Application_Model_ProjectStatus::FINISHED);
      }
    }
  }
}