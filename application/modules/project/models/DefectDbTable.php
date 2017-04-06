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
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'modify_date',
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
      ->joinLeft(array('ph' => 'phase'), 'ph.id = d.phase_id', $this->_createAlias('phase', array(
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
      ->joinLeft(array('de' => 'defect_environment'), 'de.defect_id = d.id', array())
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
      ->join(array('r' => 'release'), 'r.id = d.release_id', array())
      ->join(array('ph' => 'phase'), 'ph.id = d.phase_id', array())
      ->joinLeft(array('de' => 'defect_environment'), 'de.defect_id = d.id', array());

    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getForView($id)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'priority',
        'create_date',
        'modify_date',
        'title',
        'description'
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'id',
        'prefix'
      )))
      ->joinLeft(array('ph' => 'phase'), 'ph.id = d.phase_id', $this->_createAlias('phase', array(
        'id',
        'name'
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
      ->where('d.id = ?', $id)
      ->group('d.id')
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getForEdit(Application_Model_Defect $defect)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'priority',
        'title',
        'description',
        'assigneeName' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname, " (", assignee.email, ")")')
      ))
      ->joinLeft(array('ph' => 'phase'), 'ph.id = d.phase_id', array(
        'phaseId' => 'id',
        'phaseName' => 'name'
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
      ->where('d.id = ?', $defect->getId())
      ->where('d.project_id = ?', $defect->getProject()->getId())
      ->where('d.status NOT IN(?)', array(
        Application_Model_DefectStatus::SUCCESS,
        Application_Model_DefectStatus::FAIL
      ))
      ->limit(1)
      ->group('d.id')
      ->setIntegrityCheck(false);
  
    return $this->fetchRow($sql);
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
  
  public function getByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'ordinal_no',
        'title',
        'status'
      ))
      ->join(array('td' => 'task_defect'), 'td.defect_id = d.id', array())
      ->join(array('p' => 'project'), 'p.id = d.project_id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->where('td.bug_tracker_id IS NULL')
      ->where('td.task_id = ?', $taskId)
      ->group('d.id')
      ->order('d.ordinal_no')
      ->setIntegrityCheck(false);

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
        'objectNumber'  => new Zend_Db_Expr('CONCAT(p.prefix, "-", d.ordinal_no)')
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', array())
      ->where('d.id = ?', $defectId)
      ->where('d.project_id = ?', $projectId)
      ->group('d.id')
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
}