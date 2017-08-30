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
class Administration_Model_UserDbTable extends Custom_Model_DbTable_Criteria_AbstractList
{
  protected $_name = 'user';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'firstname',
        'lastname',
        'email',
        'status',
        'reset_password',
        'administrator',
        'organization',
        'department',
        'phone_number',
        'create_date',
        'last_login_date'
      ))
      ->where('u.id > 1')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    
    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(u.id)'))
      ->where('u.id > 1');
    
    $this->_setWhereCriteria($sql, $request);
    return $sql;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(u.firstname, " ", u.lastname, " (", u.email, ")")')
      ))
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    
    
    return $this->fetchAll($sql);
  }
  
  public function getSqlByIds(array $ids)
  {
    return $ids;
  }
  
  public function getForPopulateByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(u.firstname, " ", u.lastname, " (", u.email, ")")')
      ))
      ->where('u.id IN (?)', $ids)
      ->order('u.id')
      ->limit(count($ids));
    
    return $this->fetchAll($sql);
  }
  
  public function getForEdit($id)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'firstname',
        'lastname',
        'email',
        'status',
        'administrator',
        'organization',
        'department',
        'phone_number',
      ))
      ->where('u.id = ?', $id);
      
    return $this->fetchRow($sql);
  }

  public function getForExportByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email',
        'firstname',
        'lastname',
        'administrator',
        'organization',
        'department',
        'phone_number'
      ))
      ->join(array('ru' => 'role_user'), 'ru.user_id = u.id', array())
      ->join(array('r' => 'role'), 'r.id = ru.role_id', array())
      ->where('r.project_id = ?', $projectId)
      ->group(('u.id'))
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }

  public function getTaskAuthorsForExportByProject($projectId)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email',
        'firstname',
        'lastname',
        'administrator',
        'organization',
        'department',
        'phone_number'
      ))
      ->join(array('t' => 'task'), 't.author_id = u.id', array())
      ->where('t.project_id = ?', $projectId)
      ->where('t.status != ?', Application_Model_TaskStatus::DELETED)
      ->group(('u.id'))
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getByEmailsForImport(array $emails)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email'
      ))
      ->where('u.email IN (?)', $emails);
    
    return $this->fetchAll($sql);
  }
}