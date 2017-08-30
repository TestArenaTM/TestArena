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
        'modify_date',
        'title',
        'assigner'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assigner.firstname, " ", assigner.lastname)'),
        'assignee'.self::TABLE_CONNECTOR.'name' => new Zend_Db_Expr('CONCAT(assignee.firstname, " ", assignee.lastname)'),
        'attachmentCount' => new Zend_Db_Expr($attachmentCount)
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
      ->join(array('de' => 'defect_environment'), 'de.defect_id = d.id', array())
      ->join(array('dv' => 'defect_version'), 'dv.defect_id = d.id', array())
      ->joinLeft(array('dt' => 'defect_tag'), 'dt.defect_id = d.id', array());

    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getAllIds(Zend_Controller_Request_Abstract $request)
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
      ->group('d.id')
      ->setIntegrityCheck(false);

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
  
  public function getForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('d' => $this->_name), array(
        'id',
        'project_id',
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
      ->order('d.title')
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
        'objectNumber'  => new Zend_Db_Expr('CONCAT(p.prefix, "-", d.ordinal_no)')
      ))
      ->join(array('p' => 'project'), 'p.id = d.project_id', array())
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
}