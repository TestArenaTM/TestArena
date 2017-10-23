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
class User_Model_UserDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'user';
  
  public function getForPopulateByIds(array $ids)
  {
    $sql = $this->select();
    
    $sql->from(array('u' => $this->_name), array('id', 'email'));
    
    $sql->where('u.id IN (?)', $ids)
      ->where('u.status = ?', Application_Model_UserStatus::ACTIVE)
      ->limit(count($ids));
    
    return $this->fetchAll($sql);
  }

  public function getByEmail($email)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email',
        'reset_password',
        'status',
        'create_date',
        'last_login_date',
        'firstname',
        'lastname',
        'administrator',
        'organization',
        'department',
        'phone_number',
        'default_project_id'
      ))
      ->where('u.email = ?', $email);

    return $this->fetchRow($sql);
  }
  
  public function getIdByTokenEmail($token, $email)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array('id'))
      ->where('u.token = ?', $token)
      ->where('u.email = ?', $email)
      ->limit(1);
    
    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getNewEmailByTokenEmail($token, $email)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array('new_email'))
      ->where('u.token = ?', $token)
      ->where('u.email = ?', $email)
      ->limit(1);
    
    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getForRecoverPassword($email)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id', 
        'firstname',
        'lastname'
      ))
      ->where('u.email = ?', $email);
    
    return $this->fetchRow($sql);
  }
    
  public function getStatusByEmail($email)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'status'
      ))
      ->where('email = ?', $email);
    
    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email'
      ))
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    
    
    return $this->fetchAll($sql);
  }
  
  public function getAllAjaxForMessage(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(firstname, " ", lastname, " (", email, ")")')
      ))
      ->join(array('ru' => 'role_user'), 'ru.user_id = u.id', array())
      ->join(array('r' => 'role'), 'r.id = ru.role_id', array())
      ->where('u.id != ?', $request->getParam('currentUserId'))
      ->group('u.id')
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    
    
    return $this->fetchAll($sql);
  }
  
  //for notifications
  
  public function getAllForNotification(Custom_Interface_Notification $notification)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email',
        'firstname',
        'lastname'
      ))
      ->where('u.status IN (?)', array(Application_Model_UserStatus::ACTIVE)
      )
      ->setIntegrityCheck(false);
    
    if ($notification->getNotificationRule()->getType()->getId() != Application_Model_NotificationRuleType::CUSTOM)
    {
      $this->_setNotificationObjectCondition($sql, $notification);
    }
      
    return $this->fetchAll($sql);
  }
  
  public function getAllBlockedForNotification(Custom_Interface_Notification $notification)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'email',
        'firstname',
        'lastname'
      ))
      ->where('u.status IN (?)', array(Application_Model_UserStatus::BLOCKED))
      ->setIntegrityCheck(false);
    
    if ($notification->getNotificationRule()->getType()->getId() != Application_Model_NotificationRuleType::CUSTOM)
    {
      $this->_setNotificationObjectCondition($sql, $notification);
    }
      
    return $this->fetchAll($sql);
  }
  
  
}