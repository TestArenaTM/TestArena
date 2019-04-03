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
class Project_Model_DefectDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'defect';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $attachmentCount = '(SELECT COUNT(*) FROM attachment AS a WHERE a.subject_id = d.id AND a.type = '.Application_Model_AttachmentType::DEFECT_ATTACHMENT.')';
    
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'type',
        'modify_date',
        'title',
        'assigner'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assigner.firstname, " ", assigner.lastname)'),
        'assignee'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname)'),
        'attachmentCount' => new Zend_Db_Expr($attachmentCount),
        'taskDefectOrTestDefectIs' => new Zend_Db_Expr("
          CASE WHEN EXISTS (
            SELECT defect_id FROM `test_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
            UNION
            SELECT defect_id FROM `task_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
          )
          THEN 1
          ELSE 0
        END
        ")
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->joinLeft(array('r' => 'release'), 'r.id = d.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = d.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = d.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('author' => 'user'), 'author.id = d.author_id', $this->_createAlias('author', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('de' => 'defect_environment'), 'de.defect_id = d.id', array())
      ->join(array('dv' => 'defect_version'), 'dv.defect_id = d.id', array())
      ->joinLeft(array('dt' => 'defect_tag'), 'dt.defect_id = d.id', array())
      ->group('d.id')
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(DISTINCT d.id)'))
      ->join(array('p' => 'project'), 'p.id = d.project_id', array())
      ->joinLeft(array('r' => 'release'), 'r.id = d.release_id', array())
      ->join(array('assigner' => 'user'), 'assigner.id = d.assigner_id', array())
      ->join(array('assignee' => 'user'), 'assignee.id = d.assignee_id', array())
      ->join(array('author' => 'user'), 'author.id = d.author_id', array())
      ->join(array('de' => 'defect_environment'), 'de.defect_id = d.id', array())
      ->join(array('dv' => 'defect_version'), 'dv.defect_id = d.id', array())
      ->joinLeft(array('dt' => 'defect_tag'), 'dt.defect_id = d.id', array());

    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getAllIds(Zend_Controller_Request_Abstract $request, $user, $accessPermissionsForDefects)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'title',
        'assigner'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assigner.firstname, " ", assigner.lastname)'),
        'assignee'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname)')
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'release'), 'r.id = d.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = d.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = d.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('author' => 'user'), 'author.id = d.author_id', $this->_createAlias('author', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('de' => 'defect_environment'), 'de.defect_id = d.id', array())
      ->join(array('dv' => 'defect_version'), 'dv.defect_id = d.id', array())
      ->joinLeft(array('dt' => 'defect_tag'), 'dt.defect_id = d.id', array())
      ->where('NOT EXISTS (
            SELECT defect_id FROM `test_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
            UNION
            SELECT defect_id FROM `task_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
          )')
      ->where('d.status NOT IN ('.
        Application_Model_DefectStatus::FAIL .', '.
        Application_Model_DefectStatus::SUCCESS .')')
      ->group('d.id')
      ->setIntegrityCheck(false);

    if (!$accessPermissionsForDefects[Application_Model_RoleAction::DEFECT_DELETE_ALL])
    {
      if ($accessPermissionsForDefects[Application_Model_RoleAction::DEFECT_DELETE_CREATED_BY_YOU]
        && !$accessPermissionsForDefects[Application_Model_RoleAction::DEFECT_DELETE_ASSIGNED_TO_YOU])
      {
        $sql->where('d.author_id = ?', $user->getId());
      }
      elseif ($accessPermissionsForDefects[Application_Model_RoleAction::DEFECT_DELETE_ASSIGNED_TO_YOU]
        && !$accessPermissionsForDefects[Application_Model_RoleAction::DEFECT_DELETE_CREATED_BY_YOU]) {
        $sql->where('d.assignee_id = ?', $user->getId());
      }
      elseif ($accessPermissionsForDefects[Application_Model_RoleAction::DEFECT_DELETE_ASSIGNED_TO_YOU]
        && $accessPermissionsForDefects[Application_Model_RoleAction::DEFECT_DELETE_CREATED_BY_YOU])
      {
        $sql->where(
          $this->_db->quoteInto('d.assignee_id = ?', $user->getId())
          .' OR '.
          $this->_db->quoteInto('d.author_id = ?', $user->getId())
        );
      }
      else
      {
        return false;
      }
    }

    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);
    return $this->fetchAll($sql);
  }
  
  public function getForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'type',
        'priority',
        'create_date',
        'modify_date',
        'title',
        'description',
        'taskDefectOrTestDefectIs' => new Zend_Db_Expr("
          CASE WHEN EXISTS (
            SELECT defect_id FROM `test_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
            UNION
            SELECT defect_id FROM `task_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
          )
          THEN 1
          ELSE 0
        END
        ")
      ))
      ->joinLeft(array('r' => 'release'), 'r.id = d.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = d.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = d.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('author' => 'user'), 'author.id = d.author_id', $this->_createAlias('author', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('d.id = ?', $id)
      ->where('d.project_id = ?', $projectId)
      ->group('d.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }

  public function getForDelete($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'type',
        'priority',
        'create_date',
        'modify_date',
        'title',
        'description'
      ))
      ->joinLeft(array('r' => 'release'), 'r.id = d.release_id', $this->_createAlias('release', array(
        'id',
        'name'
      )))
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->join(array('assigner' => 'user'), 'assigner.id = d.assigner_id', $this->_createAlias('assigner', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('assignee' => 'user'), 'assignee.id = d.assignee_id', $this->_createAlias('assignee', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->join(array('author' => 'user'), 'author.id = d.author_id', $this->_createAlias('author', array(
        'id',
        'email',
        'firstname',
        'lastname'
      )))
      ->where('d.id = ?', $id)
      ->where('d.project_id = ?', $projectId)
      ->where('NOT EXISTS (
        SELECT defect_id FROM `test_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
        UNION
        SELECT defect_id FROM `task_defect` WHERE defect_id = d.id AND bug_tracker_id IS NULL
      )')
      ->group('d.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'project_id',
        'type',
        'priority',
        'title',
        'description',
        'assigneeName' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname, " (", assignee.email, ")")')
      ))
      ->joinLeft(array('r' => 'release'), 'r.id = d.release_id', array(
        'releaseId' => 'id',
        'releaseName' => 'name'
      ))
      ->join(array('assignee' => 'user'), 'assignee.id = d.assignee_id', array(
        'assigneeId' => 'id'
      ))
      ->join(array('assigner' => 'user'), 'assigner.id = d.assigner_id', array(
        'assignerId' => 'id'
      ))
      ->join(array('author' => 'user'), 'author.id = d.author_id', array(
        'authorId' => 'id'
      ))
      ->where('d.id = ?', $id)
      ->where('d.project_id = ?', $projectId)
      ->where('d.status NOT IN(?)', array(
        Application_Model_DefectStatus::SUCCESS,
        Application_Model_DefectStatus::FAIL
      ))
      ->limit(1)
      ->group('d.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }

  public function getByTask($taskId)
  {
    /* test defect */
    $sqls[] = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'title',
        'issueType' => 'type',
        'status',
        'test_id' => new Zend_Db_Expr('t.id'),
        'test_type' => new Zend_Db_Expr('t.type'),
        'test_ordinal_no' => new Zend_Db_Expr('t.ordinal_no'),
        'test_name' => new Zend_Db_Expr('t.name'),
        'task_test_id' => new Zend_Db_Expr('tt.id'),
        'task_defect_id' => new Zend_Db_Expr(0),
        'resolution_id' => new Zend_Db_Expr('tt.resolution_id'),
        'resolution_color' => new Zend_Db_Expr('r.color'),
        'resolution_name' => new Zend_Db_Expr('r.name'),
      ))
      ->join(array('td' => 'test_defect'), 'td.defect_id = d.id', array())
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->join(array('tt' => 'task_test'), 'tt.id = td.task_test_id', array())
      ->join(array('t' => 'test'), 't.id = tt.test_id', array())
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', array())
      ->where('tt.task_id = ?', $taskId)
      ->where('td.bug_tracker_id IS NULL')
      ->setIntegrityCheck(false);

    /* task defect */
    $sqls[] = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'title',
        'issueType' => 'type',
        'status',
        'test_id' => new Zend_Db_Expr(0),
        'test_type' => new Zend_Db_Expr(0),
        'test_ordinal_no' => new Zend_Db_Expr(0),
        'test_name' => new Zend_Db_Expr(0),
        'task_test_id' => new Zend_Db_Expr(0),
        'task_defect_id' => new Zend_Db_Expr('td.id'),
        'resolution_id' => new Zend_Db_Expr(0),
        'resolution_color' => new Zend_Db_Expr(0),
        'resolution_name' => new Zend_Db_Expr(0),
      ))
      ->join(array('td' => 'task_defect'), 'td.defect_id = d.id', array())
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->where('td.bug_tracker_id IS NULL')
      ->where('td.task_id = ?', $taskId)
      ->setIntegrityCheck(false);

    return $this->fetchAll($this->union($sqls)->order('ordinal_no ASC')->order('title ASC')->order('test_name ASC'));
  }

  public function getByTaskTest($taskTestId)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'title',
        'status',
        'issueType' => 'type'
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->join(array('td' => 'test_defect'), 'td.defect_id = d.id', array())
      ->where('td.task_test_id = ?', $taskTestId)
      ->where('td.bug_tracker_id IS NULL')
      ->group('d.id')
      ->order('d.ordinal_no')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request, $projectId)
  {
    $this->_setRequest($request);
    
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(p.prefix, "-", d.ordinal_no, " ", d.title)')
      ))
      ->join(array('p' => 'project'), 'd.project_id = p.id', array())
      ->where('d.project_id = ?', $projectId)
      ->group('d.id')
      ->order('d.ordinal_no ASC')
      ->order('d.title ASC')
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql, $request);      
    return $this->fetchAll($sql);
  }  
  
  public function getByOrdinalNoForAjax($ordinalNo, $projectId)
  {    
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(p.prefix, "-", d.ordinal_no, " ", d.title)')
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', array())
      ->where('d.ordinal_no = ?', $ordinalNo)
      ->where('d.project_id = ?', $projectId)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getForViewAjax($defectId, $projectId)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'status',
        'name'          => 'title',
        'objectNumber'  => new Zend_Db_Expr('CONCAT(p.prefix, "-", d.ordinal_no)'),
        'issueType'     => 'type'
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->where('d.id = ?', $defectId)
      ->where('d.project_id = ?', $projectId)
      ->group('d.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getByIds4CheckAccess(array $ids)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'status',
        'project'.self::TABLE_CONNECTOR.'id' => 'project_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id'
      ))
      ->where('d.id IN (?)', $ids)      
      ->limit(count($ids))
      ->group('d.id')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }

  public function getByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'title',
      ))
      ->where('d.id IN (?)', $ids)
      ->group('d.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}