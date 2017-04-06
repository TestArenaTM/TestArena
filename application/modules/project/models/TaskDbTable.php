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
class Project_Model_TaskDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'task';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'modify_date',
        'due_date',
        'title',
        'assigner'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assigner.firstname, " ", assigner.lastname)'),
        'assignee'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname)')
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->joinLeft(array('ph' => 'phase'), 'ph.id = t.phase_id', $this->_createAlias('phase', array(
        'id',
        'name'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = t.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = t.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('author' => 'user'), 'author.id = t.author_id', $this->_createAlias('author', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->joinLeft('resolution', 'resolution.id = t.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->joinLeft(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      //->where('t.assignee_id = ?', $request->getParam('userId'))
      ->group('t.id')
      ->setIntegrityCheck(false);
    $this->_setWhereCriteria($sql, $request);

    //$sql = $this->union($sql)->group('id');
    $this->_setOrderConditions($sql, $request);

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql1 = $this->select()
      ->from(array('t' => $this->_name), array('id'))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', array())
      ->joinLeft(array('ph' => 'phase'), 'ph.id = t.phase_id', array())
      ->joinLeft(array('te' => 'task_environment'), 'te.task_id = t.id', array()) 
      ->where('t.assigner_id = ?', $request->getParam('userId'));

    $this->_setWhereCriteria($sql1, $request);

    $sql2 = $this->select()
      ->from(array('t' => $this->_name), array('id'))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', array())
      ->joinLeft(array('ph' => 'phase'), 'ph.id = t.phase_id', array())
      ->joinLeft(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      ->where('t.assignee_id = ?', $request->getParam('userId'));

    $this->_setWhereCriteria($sql2, $request);
    
    return $this->select()->from(
      $this->select()->from($this->select()->union(array('('.$sql1.')', '('.$sql2.')'), Zend_Db_Select::SQL_UNION_ALL))
        ->group('id')
        ->setIntegrityCheck(false), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(DISTINCT id)'))
        ->setIntegrityCheck(false);
  }
  
  public function getAllIds(Zend_Controller_Request_Abstract $request)
  {
    $sql[0] = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'modify_date',
        'due_date',
        'title',
        'assigner'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assigner.firstname, " ", assigner.lastname)'),
        'assignee'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname)')
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->joinLeft(array('ph' => 'phase'), 'ph.id = t.phase_id', $this->_createAlias('phase', array(
        'id',
        'name'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = t.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = t.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->joinLeft(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      ->where('t.assignee_id = ?', $request->getParam('userId'))
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql[0], $request);

    $sql[1] = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'modify_date',
        'due_date',
        'title',
        'assigner'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assigner.firstname, " ", assigner.lastname)'),
        'assignee'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname)')
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->joinLeft(array('ph' => 'phase'), 'ph.id = t.phase_id', $this->_createAlias('phase', array(
        'id',
        'name'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = t.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = t.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->joinLeft(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      ->where('t.assigner_id = ?', $request->getParam('userId'))
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql[1], $request);    
    $sql = $this->union($sql)->group('id');

    $this->_setOrderConditions($sql, $request);
    return $this->fetchAll($sql);
  }
  
  public function getForEdit(Application_Model_Task $task)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'priority',
        'due_date',
        'title',
        'description',
        'assigneeName' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname, " (", assignee.email, ")")')
      ))
      ->joinLeft(array('ph' => 'phase'), 'ph.id = t.phase_id', array(
        'phaseId' => 'id',
        'phaseName' => 'name'
      ))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', array(
        'releaseId' => 'id',
        'releaseName' => 'name'
      ))
      ->join(array('assignee' => 'user'), 'assignee.id = t.assignee_id', array(
        'assigneeId' => 'id'
      ))
      ->join(array('assigner' => 'user'), 'assigner.id = t.assigner_id', array(
        'assignerId' => 'id'
      ))
      ->join(array('author' => 'user'), 'author.id = t.author_id', array(
        'authorId' => 'id'
      ))
      ->where('t.id = ?', $task->getId())
      ->where('t.project_id = ?', $task->getProject()->getId())
      ->where('t.status != ?', Application_Model_TaskStatus::CLOSED)
      ->limit(1)
      ->group('t.id')
      ->setIntegrityCheck(false);
  
    return $this->fetchRow($sql);
  }
  
  public function getAllByRelease(Application_Model_Release $release, $returnSql = false)
  {
    $sql = $this->select()
      ->from(array('ta' => $this->_name), array(
        'id',
        'priority',
        'title',
        'description',
        'resolution$id' => 'resolution_id',
        'author$id' => 'author_id'
      ))
      ->joinLeft(array('tt' => 'task_test'), 'ta.id = tt.task_id', $this->_createAlias('taskTest', array(
        'resolution_id'
      )))
      ->joinLeft(array('te' => 'test'), 'te.id = tt.test_id', $this->_createAlias('test', array(
        'id',
        'status'
      )))
      ->joinLeft(array('at' => 'attachment'), 'at.type = '.Application_Model_AttachmentType::TASK_ATTACHMENT.' AND at.subject_id = ta.id', $this->_createAlias('attachment', array(
        'file_id',
        'create_date'
      )))
      ->where('ta.project_id = ?', $release->getProjectId())
      ->where('ta.release_id = ?', $release->getExtraData('oldReleaseId'))
      ->order('ta.id ASC')
      ->setIntegrityCheck(false);
    
    if ($returnSql)
    {
      return $sql;
    }
    else
    {
      return $this->fetchAll($sql);
    }
  }
  
  public function getByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'due_date',
        'title',
        'description',
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->where('t.id IN (?)', $ids)
      ->limit(count($ids))
      ->group('t.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getForView($id)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'due_date',
        'title',
        'description'
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'id',
        'prefix'
      )))
      ->joinLeft(array('ph' => 'phase'), 'ph.id = t.phase_id', $this->_createAlias('phase', array(
        'id',
        'name'
      )))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = t.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = t.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('author' => 'user'), 'author.id = t.author_id', $this->_createAlias('author', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->joinLeft('resolution', 'resolution.id = t.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->where('t.id = ?', $id)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
}