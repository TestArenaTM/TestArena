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
class Project_Model_TestDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'test';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $attachmentCount = '(SELECT COUNT(*) FROM attachment AS a WHERE a.subject_id = t.id AND a.type = '.Application_Model_AttachmentType::TEST_ATTACHMENT.')';
    
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'type',
        'create_date',
        'name',
        'attachmentCount' => new Zend_Db_Expr($attachmentCount)
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->join(array('a' => 'user'), 't.author_id = a.id', $this->_createAlias('author', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('t.project_id = ?', $request->getParam('projectId'))
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.current_version = ?', true)
      ->group('t.id')
      ->setIntegrityCheck(false);
    
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);

    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(t.id)'))
      ->where('t.project_id = ?', $request->getParam('projectId'))
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.current_version = ?', true)
      ->setIntegrityCheck(false);
    
    $this->_setWhereCriteria($sql, $request);
    
    return $sql;
  }
  
  public function getAllIds(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'status',
        'type',
        'create_date',
        'name'
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->join(array('a' => 'user'), 't.author_id = a.id', $this->_createAlias('author', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('t.project_id = ?', $request->getParam('projectId'))
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.current_version = ?', true)
      ->group('t.id')
      ->setIntegrityCheck(false);
    
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);

    return $this->fetchAll($sql);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request, $projectId)
  {
    $this->_setRequest($request);
    
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(p.prefix, "-", t.ordinal_no, " ", t.name)')
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.current_version = 1')
      ->where('t.project_id = ?', $projectId)
      ->group('t.id')
      ->order('t.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);      
    return $this->fetchAll($sql);
  }  
  
  public function getForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'name',
        'type',
        'status'
      ))
      ->join(array('u' => 'user'), 't.author_id = u.id', $this->_createAlias('author', array(
        'id'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getOtherTestForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'name',
        'type',
        'author_id',
        'create_date',
        'status',
        'family_id',
        'current_version',
        'description'
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->join(array('a' => 'user'), 't.author_id = a.id', $this->_createAlias('author', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::OTHER_TEST)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }

  public function getTestCaseForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'name',
        'type',
        'author_id',
        'create_date',
        'status',
        'family_id',
        'current_version',
        'description'
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->join(array('tc' => 'test_case'), 'tc.test_id = t.id', array(
        'presuppositions',
        'result'
      ))
      ->join(array('a' => 'user'), 't.author_id = a.id', $this->_createAlias('author', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::TEST_CASE)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getExploratoryTestForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'name',
        'type',
        'author_id',
        'create_date',
        'status',
        'family_id',
        'current_version'
      ))
      ->join(array('et' => 'exploratory_test'), 'et.test_id = t.id', array(
        'duration',
        'test_card'
      ))
      ->join(array('a' => 'user'), 't.author_id = a.id', $this->_createAlias('author', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::EXPLORATORY_TEST)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getAutomaticTestForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'name',
        'type',
        'author_id',
        'create_date',
        'status',
        'family_id',
        'current_version',
        'description'
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->join(array('a' => 'user'), 't.author_id = a.id', $this->_createAlias('author', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::AUTOMATIC_TEST)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getChecklistForView($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'ordinal_no',
        'name',
        'type',
        'author_id',
        'create_date',
        'status',
        'family_id',
        'current_version',
        'description'
      ))
      ->join(array('p' => 'project'), 't.project_id = p.id', $this->_createAlias('project', array(
        'prefix'
      )))
      ->join(array('a' => 'user'), 't.author_id = a.id', $this->_createAlias('author', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::CHECKLIST)
      ->group('t.id')
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getOtherTestForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'type',
        'status',
        'name',
        'description',
        'ordinal_no',
        'family_id',
        'current_version'
      ))
      ->join(array('u' => 'user'), 't.author_id = u.id', $this->_createAlias('author', array(
        'id'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::OTHER_TEST)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }

  public function getTestCaseForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'type',
        'status',
        'name',
        'description',
        'ordinal_no',
        'family_id',
        'current_version'
      ))
      ->join(array('tc' => 'test_case'), 'tc.test_id = t.id', array(
          'presuppositions',
          'result'
        )
      )
      ->join(array('u' => 'user'), 't.author_id = u.id', $this->_createAlias('author', array(
        'id'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::TEST_CASE)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getExploratoryTestForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'type',
        'status',
        'name',
        'ordinal_no',
        'family_id',
        'current_version'
      ))
      ->join(array('et' => 'exploratory_test'), 'et.test_id = t.id', array(
          'duration',
          'test_card'
        )
      )
      ->join(array('u' => 'user'), 't.author_id = u.id', $this->_createAlias('author', array(
        'id'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::EXPLORATORY_TEST)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getAutomaticTestForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'type',
        'status',
        'name',
        'description',
        'ordinal_no',
        'family_id',
        'current_version'
      ))
      ->join(array('u' => 'user'), 't.author_id = u.id', $this->_createAlias('author', array(
        'id'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::AUTOMATIC_TEST)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getChecklistForEdit($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'type',
        'status',
        'name',
        'description',
        'ordinal_no',
        'family_id',
        'current_version'
      ))
      ->join(array('u' => 'user'), 't.author_id = u.id', $this->_createAlias('author', array(
        'id'
      )))
      ->where('t.id = ?', $id)
      ->where('t.project_id = ?', $projectId)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.type = ?', Application_Model_TestType::CHECKLIST)
      ->limit(1)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getPreviousNextByTest($id, $projectId)
  {
    $prevSql = $this->select()
      ->from(array('p' => $this->_name),  array(
          'id',
          'type',
          'isNext' => new Zend_Db_Expr(0)
        )
      )
      ->where('p.id < ?', $id)
      ->where('p.project_id = ?', $projectId)
      ->where('p.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('p.current_version = ?', true)
      ->order('p.id DESC')
      ->limit(1)
      ->setIntegrityCheck(false);
    
    $nextSql = $this->select()
       ->from(array('n' => $this->_name),  array(
          'id',
          'type',
          'isNext' => new Zend_Db_Expr(1)
        )
      )
      ->where('n.id > ?', $id)
      ->where('n.project_id = ?', $projectId)
      ->where('n.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('n.current_version = ?', true)
      ->order('n.id ASC')
      ->limit(1)
      ->setIntegrityCheck(false);
    
    $sql = $this->select()->union(array('('.$prevSql.')', '('.$nextSql.')'))->setIntegrityCheck(false);
    return $this->fetchAll($sql);
  }
  
  public function getForViewInTask($id, $projectId)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'type'
      ))
      ->where('t.id = ?', $id)
      ->where('t.status = ?', Application_Model_TestStatus::ACTIVE)
      ->where('t.project_id = ?', $projectId)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getByIds4CheckAccess(array $ids)
  {
    $sql = $this->select()
      ->from(array('t' => $this->_name), array(
        'id',
        'status',
        'project'.self::TABLE_CONNECTOR.'id' => 'project_id',
        'author'.self::TABLE_CONNECTOR.'id' => 'author_id'
      ))
      ->where('t.id IN (?)', $ids)      
      ->limit(count($ids))
      ->group('t.id')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
}