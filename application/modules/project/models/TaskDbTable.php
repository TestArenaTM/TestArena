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

  public function getAllAjax(Zend_Controller_Request_Abstract $request, $projectId)
  {
    $this->_setRequest($request);

    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(p.prefix, "-", t.ordinal_no, " ", t.title)')
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', array())
      ->where('t.project_id = ?', $projectId)
      ->group('t.id')
      ->order('t.title')
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql, $request);
    return $this->fetchAll($sql);
  }

  public function getSqlAll(Zend_Controller_Request_Abstract $request, $bugTrackerId)
  {
    $attachmentCount = '(SELECT COUNT(*) FROM attachment AS a WHERE a.subject_id = t.id AND a.type = '.Application_Model_AttachmentType::TASK_ATTACHMENT.')';

    if (empty($bugTrackerId))
    {
      $taskDefectOrTestDefectIs = new Zend_Db_Expr('
        CASE WHEN EXISTS (
          SELECT defect_id FROM `test_defect` AS tede JOIN `task_test` AS tate ON tate.id = tede.task_test_id WHERE t.id = tate.task_id AND bug_tracker_id IS NULL
          UNION
          SELECT defect_id FROM `task_defect` WHERE task_id = t.id AND bug_tracker_id IS NULL
        )
        THEN 1
        ELSE 0
      END
      ');
    }
    else
    {
      $bugTrackerId = (int) $bugTrackerId;
      $taskDefectOrTestDefectIs = new Zend_Db_Expr('
        CASE WHEN EXISTS (
          SELECT defect_id FROM `test_defect` AS tede JOIN `task_test` AS tate ON tate.id = tede.task_test_id WHERE t.id = tate.task_id AND bug_tracker_id = '. $bugTrackerId .'
          UNION
          SELECT defect_id FROM `task_defect` WHERE task_id = t.id AND bug_tracker_id = '. $bugTrackerId .'
        )
        THEN 1
        ELSE 0
      END
      ');
    }

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
        'assignee'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname)'),
        'attachmentCount' => new Zend_Db_Expr($attachmentCount),
        'taskDefectOrTestDefectIs' => $taskDefectOrTestDefectIs
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix',
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
      ->join(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      ->join(array('tv' => 'task_version'), 'tv.task_id = t.id', array())
      ->joinLeft(array('tt' => 'task_tag'), 'tt.task_id = t.id', array())
      ->joinLeft(array('tate' => 'task_test'), 'tate.task_id = t.id', array())
      ->group('t.id')
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);
    return $sql;
  }

  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(DISTINCT t.id)'
      ))
      ->joinLeft(array('r' => 'release'), 'r.id = t.release_id', array())
      ->join(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      ->join(array('tv' => 'task_version'), 'tv.task_id = t.id', array())
      ->joinLeft(array('tt' => 'task_tag'), 'tt.task_id = t.id', array())
      ->joinLeft(array('tate' => 'task_test'), 'tate.task_id = t.id', array())
      ->setIntegrityCheck(false);

    $this->_setWhereCriteria($sql, $request);

    return $sql;
  }

  public function getAllIds(Zend_Controller_Request_Abstract $request, $user, $accessPermissionsForTasks, $bugTrackerId)
  {
    if (empty($bugTrackerId))
    {
      $taskDefectOrTestDefectIs = new Zend_Db_Expr('
        NOT EXISTS (
          SELECT defect_id FROM `test_defect` AS tede JOIN `task_test` AS tate ON tate.id = tede.task_test_id WHERE t.id = tate.task_id AND bug_tracker_id IS NULL
          UNION
          SELECT defect_id FROM `task_defect` WHERE task_id = t.id AND bug_tracker_id IS NULL
        )
      ');
    }
    else
    {
      $bugTrackerId = (int) $bugTrackerId;
      $taskDefectOrTestDefectIs = new Zend_Db_Expr('
        NOT EXISTS (
          SELECT defect_id FROM `test_defect` AS tede JOIN `task_test` AS tate ON tate.id = tede.task_test_id WHERE t.id = tate.task_id AND bug_tracker_id = '. $bugTrackerId .'
          UNION
          SELECT defect_id FROM `task_defect` WHERE task_id = t.id AND bug_tracker_id = '. $bugTrackerId .'
        )
      ');
    }

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
      ->join(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      ->join(array('tv' => 'task_version'), 'tv.task_id = t.id', array())
      ->joinLeft(array('tt' => 'task_tag'), 'tt.task_id = t.id', array())
      ->joinLeft(array('tate' => 'task_test'), 'tate.task_id = t.id', array())
      ->where($taskDefectOrTestDefectIs)
      ->group('t.id')
      ->setIntegrityCheck(false);

    if (!$accessPermissionsForTasks[Application_Model_RoleAction::TASK_DELETE_ALL])
    {
      if ($accessPermissionsForTasks[Application_Model_RoleAction::TASK_DELETE_CREATED_BY_YOU]
        && !$accessPermissionsForTasks[Application_Model_RoleAction::TASK_DELETE_ASSIGNED_TO_YOU])
      {
        $sql->where('t.author_id = ?', $user->getId());
      }
      elseif ($accessPermissionsForTasks[Application_Model_RoleAction::TASK_DELETE_ASSIGNED_TO_YOU]
        && !$accessPermissionsForTasks[Application_Model_RoleAction::TASK_DELETE_CREATED_BY_YOU]) {
        $sql->where('t.assignee_id = ?', $user->getId());
      }
      elseif ($accessPermissionsForTasks[Application_Model_RoleAction::TASK_DELETE_ASSIGNED_TO_YOU]
        && $accessPermissionsForTasks[Application_Model_RoleAction::TASK_DELETE_CREATED_BY_YOU])
      {
        $sql->where('t.assignee_id = ?', $user->getId());
        $sql->orWhere('t.author_id = ?', $user->getId());
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

  public function getForEdit($id, $projectId)
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
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status != ?', Application_Model_TaskStatus::CLOSED)
      ->limit(1)
      ->group('t.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }

  public function getAllByRelease(Application_Model_Release $release, $returnSql = false)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'priority',
        'title',
        'description',
        'resolution$id' => 'resolution_id',
        'author$id' => 'author_id'
      ))
      ->joinLeft(array('tt' => 'task_test'), 't.id = tt.task_id', $this->_createAlias('taskTest', array(
        'resolution_id'
      )))
      ->joinLeft(array('te' => 'test'), 'te.id = tt.test_id', $this->_createAlias('test', array(
        'id',
        'status'
      )))
      ->joinLeft(array('at' => 'attachment'), 'at.type = '.Application_Model_AttachmentType::TASK_ATTACHMENT.' AND at.subject_id = t.id', $this->_createAlias('attachment', array(
        'file_id',
        'create_date'
      )))
      ->where('t.project_id = ?', $release->getProjectId())
      ->where('t.release_id = ?', $release->getExtraData('oldReleaseId'))
      ->order('t.id ASC')
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

  public function getAllByDefect(Application_Model_Defect $defect, Application_Model_Project $project)
  {
    /* tasks */
    $sqls[] = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'priority',
        'title',
        'description',
        'ordinal_no',
        'status',
        'resolution$id' => 'resolution_id',
        'author$id' => 'author_id',
        'test_id' => new Zend_Db_Expr(0),
        'test_type' => new Zend_Db_Expr(0),
        'test_ordinal_no' => new Zend_Db_Expr(0),
        'test_name' => new Zend_Db_Expr(0),
        'task_test_id' => new Zend_Db_Expr(0),
        'taskRelation' => new Zend_Db_Expr('IF(1, "defect", "")')
      ))
      ->join(array('td' => 'task_defect'), 'td.task_id = t.id', array())
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
      ->joinLeft(array('r' => 'resolution'), 'r.id = t.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'reopen_status_color',
        'closed_status_color',
        'in_progress_status_color'
      )))
      ->where('td.defect_id = ?', $defect->getId())
      ->where('td.bug_tracker_id IS NULL')
      ->where('p.id = ?', $project->getId())
      ->setIntegrityCheck(false);


    /* tasks with tests */
    $sqls[] = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'priority',
        'title',
        'description',
        'ordinal_no',
        'status',
        'resolution$id' => 'resolution_id',
        'author$id' => 'author_id',
        'test_id' => new Zend_Db_Expr('te.id'),
        'test_type' => new Zend_Db_Expr('te.type'),
        'test_ordinal_no' => new Zend_Db_Expr('te.ordinal_no'),
        'test_name' => new Zend_Db_Expr('te.name'),
        'task_test_id' => new Zend_Db_Expr('tt.id'),
        'taskRelation' => new Zend_Db_Expr('IF(1, "test", "")')
      ))
      ->join(array('tt' => 'task_test'), 'tt.task_id = t.id', array())
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
      ->join(array('td' => 'test_defect'), 'td.task_test_id = tt.id', array())
      ->join(array('te' => 'test'), 'te.id = tt.test_id', array())
      ->joinLeft(array('r' => 'resolution'), 'r.id = tt.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->join(array('p' => 'project'), 'p.id = te.project_id', $this->_createAlias('project', array(
        'prefix',
        'open_status_color',
        'reopen_status_color',
        'closed_status_color',
        'in_progress_status_color'
      )))
      ->where('td.defect_id = ?', $defect->getId())
      ->where('p.id = ?', $project->getId())
      ->where('td.bug_tracker_id IS NULL')
      ->setIntegrityCheck(false);

    return $this->fetchAll($this->union($sqls)->order('title ASC')->order('test_name ASC')->order('taskRelation ASC'));
  }

  public function getAllByReleaseByIds(array $ids, Application_Model_Release $release, $returnSql = false)
  {
    $taskVersionsSql = (count(array_filter(explode(',', $release->getExtraData('versions', null)))))
                        ? 'NULL'
                        : '(SELECT GROUP_CONCAT(version_id) FROM task_version tv INNER JOIN version AS v ON tv.version_id = v.id WHERE tv.task_id = t.id)';

    $taskEnvironmentsSql = (count(array_filter(explode(',', $release->getExtraData('environments', null)))))
                            ? 'NULL'
                            : '(SELECT GROUP_CONCAT(environment_id) FROM task_environment te INNER JOIN environment AS e ON te.environment_id = e.id WHERE te.task_id = t.id)';

    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'priority',
        'title',
        'description',
        'resolution$id' => 'resolution_id',
        'author$id' => 'author_id',
        'tags' => new Zend_Db_Expr('(SELECT GROUP_CONCAT(tag_id) FROM task_tag ttg INNER JOIN tag AS tg ON tg.id = ttg.tag_id WHERE ttg.task_id = t.id)'),
        'environments' => new Zend_Db_Expr($taskEnvironmentsSql),
        'versions' => new Zend_Db_Expr($taskVersionsSql)
      ))
      ->joinLeft(array('tt' => 'task_test'), 't.id = tt.task_id', $this->_createAlias('taskTest', array(
        'resolution_id',
      )))
      ->joinLeft(array('te' => 'test'), 'te.id = tt.test_id', $this->_createAlias('test', array(
        'id',
        'status',
        'checklist_items' => new Zend_Db_Expr('(SELECT GROUP_CONCAT(id) FROM checklist_item ci WHERE ci.test_id = te.id)')
      )))
      ->joinLeft(array('at' => 'attachment'), 'at.type = '.Application_Model_AttachmentType::TASK_ATTACHMENT.' AND at.subject_id = t.id', $this->_createAlias('attachment', array(
        'file_id',
        'create_date'
      )))
      ->where('t.project_id = ?', $release->getProjectId())
      ->where('t.release_id = ?', $release->getExtraData('oldReleaseId'))
      ->where('t.id IN (?)', $ids)
      ->order('t.id ASC')
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

  public function getByIds(array $ids, $returnSql = false)
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

    if ($returnSql)
    {
      return $sql;
    }
    else
    {
      return $this->fetchAll($sql);
    }
  }

  public function getByIds4CheckAccess(array $ids)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'status',
        'project'.self::TABLE_CONNECTOR.'id' => 'project_id',
        'assigner'.self::TABLE_CONNECTOR.'id' => 'assigner_id',
        'assignee'.self::TABLE_CONNECTOR.'id' => 'assignee_id',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id'
      ))
      ->where('t.id IN (?)', $ids)
      ->limit(count($ids))
      ->group('t.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }

  public function getForView($id, $projectId, $bugTrackerId)
  {
    if (empty($bugTrackerId))
    {
      $taskDefectOrTestDefectIs = new Zend_Db_Expr('
        CASE WHEN EXISTS (
          SELECT defect_id FROM `test_defect` AS tede JOIN `task_test` AS tate ON tate.id = tede.task_test_id WHERE t.id = tate.task_id AND bug_tracker_id IS NULL
          UNION
          SELECT defect_id FROM `task_defect` WHERE task_id = t.id AND bug_tracker_id IS NULL
        )
        THEN 1
        ELSE 0
      END
      ');
    }
    else
    {
      $bugTrackerId = (int) $bugTrackerId;
      $taskDefectOrTestDefectIs = new Zend_Db_Expr('
        CASE WHEN EXISTS (
          SELECT defect_id FROM `test_defect` AS tede JOIN `task_test` AS tate ON tate.id = tede.task_test_id WHERE t.id = tate.task_id AND bug_tracker_id = '. $bugTrackerId .'
          UNION
          SELECT defect_id FROM `task_defect` WHERE task_id = t.id AND bug_tracker_id = '. $bugTrackerId .'
        )
        THEN 1
        ELSE 0
      END
      ');
    }

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
        'taskDefectOrTestDefectIs' => $taskDefectOrTestDefectIs,
      ))
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
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'open_status_color',
        'in_progress_status_color',
        'reopen_status_color',
        'closed_status_color',
        'invalid_status_color',
        'resolved_status_color'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }

  public function getForPdfReportByRelease(Application_Model_Release $release)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'title',
        'environments' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT e.name SEPARATOR ", ")'),
        'versions' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT v.name SEPARATOR ", ")')
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->joinLeft(array('r' => 'resolution'), 'r.id = t.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->join(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      ->join(array('e' => 'environment'), 'e.id = te.environment_id', array())
      ->join(array('tv' => 'task_version'), 'tv.task_id = t.id', array())
      ->join(array('v' => 'version'), 'v.id = tv.version_id', array())
      ->where('t.release_id = ?', $release->getId())
      ->group('t.id')
      ->order('t.id ASC')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }

  public function getForCsvReportByRelease(Application_Model_Release $release)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'title'
      ))
      ->join(array('p' => 'project'), 'p.id = t.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->where('t.release_id = ?', $release->getId())
      ->group('t.id')
      ->order('t.id ASC')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }

  public function getTasks4ReleaseCloneByRelease(Application_Model_Release $release, $returnSql = false)
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
      ->joinLeft('resolution', 'resolution.id = t.resolution_id', $this->_createAlias('resolution', array(
        'id',
        'name',
        'color'
      )))
      ->joinLeft(array('te' => 'task_environment'), 'te.task_id = t.id', array())
      //->where('t.assignee_id = ?', $request->getParam('userId'))
      ->where('t.release_id = ?', $release->getId())
      ->group('t.id')
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

  public function getForDeleteDefect($taskTestId)
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
        'test_id' => 'te.id',
      ))
      ->join(array('tt' => 'task_test'), 't.id = tt.task_id', array())
      ->join(array('te' => 'test'), 'te.id = tt.test_id', array())
      ->where('tt.id = ?', $taskTestId)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
}