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
class Message_Model_MessageDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'message';
  
  public function getSqlAll(Zend_Controller_Request_Abstract $request)
  {  
    $sql1 = $this->select()
      ->from(array('m' => 'message'), array(
        'id' => new Zend_Db_Expr('max(m.id)'),
        'subject',
        'content' => new Zend_Db_Expr('NULL'),
        'create_date' => new Zend_Db_Expr('max(m.create_date)'),
        'thread_id',
        'user_status' => new Zend_Db_Expr('100'),
        'message_type' => new Zend_Db_Expr(Application_Model_Message::TYPE_MESSAGE_SENT),
        'status',
        'sent_date' => new Zend_Db_Expr('NULL')
      ))
      ->join(array('u' => 'user'), 'u.id = m.recipient_id', $this->_createAlias('user', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('m.sender_id = ?', $request->getParam('user_id'))
      ->where('m.from_status = ?', Application_Model_MessageFromStatus::SENT)
      ->where('m.sender_type = ?', Application_Model_MessageUserType::USER)
      ->group('m.thread_id')
      ->setIntegrityCheck(false);
    
    $sql2 = $this->select()
      ->from(array('m' => 'message'), array(
        'id' => new Zend_Db_Expr('max(m.id)'),
        'subject',
        'content' => new Zend_Db_Expr('NULL'),
        'create_date' => new Zend_Db_Expr('max(m.create_date)'),
        'thread_id',
        'user_status' => new Zend_Db_Expr('min(m.to_status)'),
        'message_type' => new Zend_Db_Expr(Application_Model_Message::TYPE_MESSAGE_RECEIVED),
        'status',
        'sent_date' => new Zend_Db_Expr('NULL')
      ))
      ->join(array('u' => 'user'), 'u.id = m.sender_id', $this->_createAlias('user', array(
        'id',
        'firstname',
        'lastname',
        'email'
      )))
      ->where('m.recipient_id = ?', $request->getParam('user_id'))
      ->where('m.to_status IN (?)', array(Application_Model_MessageToStatus::UNREAD,
                                              Application_Model_MessageToStatus::READ))
      ->where('m.recipient_type = ?', Application_Model_MessageUserType::USER)
      ->group('m.thread_id')
      ->setIntegrityCheck(false);
    
    $sql = $this->select()
      ->from($this->select()->union(array('('.$sql1.')', '('.$sql2.')'), Zend_Db_Select::SQL_UNION_ALL),
        array(
          'id' => new Zend_Db_Expr('max(id)'),
          'subject',
          'content',
          'create_date' => new Zend_Db_Expr('max(create_date)'),
          'thread_id',
          'user_status' => new Zend_Db_Expr('min(user_status)'),
          'message_type',
          'status',
          'sent_date',
          'user$id',
          'user$firstname',
          'user$lastname',
          'user$email'
        ))
      ->group('thread_id')
      ->order('create_date DESC')
      ->setIntegrityCheck(false);
    
    return $sql;
  }
  
  public function getSqlAllCount(Zend_Controller_Request_Abstract $request)
  {
    $sql1 = $this->select()
      ->from(array('m' => 'message'), array(
        'id',
        'thread_id'
      ))
      ->where('m.sender_id = ?', $request->getParam('user_id'))
      ->where('m.from_status = ?', Application_Model_MessageFromStatus::SENT)
      ->where('m.sender_type = ?', Application_Model_MessageUserType::USER)
      ->group('m.thread_id')
      ->setIntegrityCheck(false);
    
    $sql2 = $this->select()
      ->from(array('m' => 'message'), array(
        'id',
        'thread_id'
      ))
      ->where('m.recipient_id = ?', $request->getParam('user_id'))
      ->where('m.to_status NOT IN (?)', array(Application_Model_MessageToStatus::DELETED,
                                              Application_Model_MessageToStatus::DELETED_PERNAMENTLY))
      ->where('m.recipient_type = ?', Application_Model_MessageUserType::USER)
      ->group('m.thread_id')
      ->setIntegrityCheck(false);
    
   return $this->select()->from($this->select()
               ->from($this->select()->union(array('('.$sql1.')', '('.$sql2.')'), Zend_Db_Select::SQL_UNION_ALL))
               ->group('thread_id')
               ->setIntegrityCheck(false), array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(id)'))
               ->setIntegrityCheck(false);
  }
  
  public function getUsersThreadMessagesByThreadAjax($threadId, $userId)
  {
    $sql[] = $this->select()
      ->from(array('m' => $this->_name), array(
        'id',
        'thread_id',
        'create_date',
        'content',
        'message_type' => new Zend_Db_Expr(Application_Model_Message::TYPE_MESSAGE_RECEIVED)
      ))
      ->join(array('u' => 'user'), 'm.sender_id = u.id', $this->_createAlias('user', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('m.from_status = ?', Application_Model_MessageFromStatus::SENT)
      ->where('m.recipient_type = ?', Application_Model_MessageUserType::USER)
      ->where('m.thread_id = ?', $threadId)
      ->where('m.recipient_id = ?', $userId)
      ->setIntegrityCheck(false);
    
    $sql[] = $this->select()
      ->from(array('m' => $this->_name), array(
        'id',
        'thread_id',
        'create_date',
        'content',
        'message_type' => new Zend_Db_Expr(Application_Model_Message::TYPE_MESSAGE_SENT)
      ))
      ->join(array('u' => 'user'), 'm.recipient_id = u.id', $this->_createAlias('user', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('m.to_status NOT IN (?)', array(Application_Model_MessageToStatus::DELETED,
                                              Application_Model_MessageToStatus::DELETED_PERNAMENTLY))
      ->where('m.sender_type = ?', Application_Model_MessageUserType::USER)
      ->where('m.thread_id = ?', $threadId)
      ->where('m.sender_id = ?', $userId)
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($this->select()
      ->union($sql)
      ->group('id')
      ->order('id ASC')
      );
  }
  
  public function checkValidThreadByUser($threadId, $userId)
  {
    $sql[] = $this->select()
      ->from(array('m' => $this->_name), array(
        'id'
      ))
      ->join(array('u' => 'user'), 'm.sender_id = u.id', $this->_createAlias('user', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('m.from_status = ?', Application_Model_MessageFromStatus::SENT)
      ->where('m.recipient_type = ?', Application_Model_MessageUserType::USER)
      ->where('m.thread_id = ?', $threadId)
      ->where('m.recipient_id = ?', $userId)
      ->setIntegrityCheck(false);
    
    $sql[] = $this->select()
      ->from(array('m' => $this->_name), array(
        'id'
      ))
      ->join(array('u' => 'user'), 'm.recipient_id = u.id', $this->_createAlias('user', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('m.to_status NOT IN (?)', array(Application_Model_MessageToStatus::DELETED,
                                              Application_Model_MessageToStatus::DELETED_PERNAMENTLY))
      ->where('m.sender_type = ?', Application_Model_MessageUserType::USER)
      ->where('m.thread_id = ?', $threadId)
      ->where('m.sender_id = ?', $userId)
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($this->select()
      ->union($sql)
      ->group('id')
      ->order('id ASC')
      );
  }
  
  public function getSingleThreadMessageByUser($threadId, $userId)
  {
    $sql[] = $this->select()
      ->from(array('m' => $this->_name), array(
        'id',
        'subject',
        'thread_id',
        'message_type' => new Zend_Db_Expr(Application_Model_Message::TYPE_MESSAGE_RECEIVED)
      ))
      ->join(array('u' => 'user'), 'm.sender_id = u.id', $this->_createAlias('user', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('m.from_status = ?', Application_Model_MessageFromStatus::SENT)
      ->where('m.recipient_type = ?', Application_Model_MessageUserType::USER)
      ->where('m.thread_id = ?', $threadId)
      ->where('m.recipient_id = ?', $userId)
      ->setIntegrityCheck(false);
    
    $sql[] = $this->select()
      ->from(array('m' => $this->_name), array(
        'id',
        'subject',
        'thread_id',
        'message_type' => new Zend_Db_Expr(Application_Model_Message::TYPE_MESSAGE_SENT)
      ))
      ->join(array('u' => 'user'), 'm.recipient_id = u.id', $this->_createAlias('user', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('m.to_status NOT IN (?)', array(Application_Model_MessageToStatus::DELETED,
                                              Application_Model_MessageToStatus::DELETED_PERNAMENTLY))
      ->where('m.sender_type = ?', Application_Model_MessageUserType::USER)
      ->where('m.thread_id = ?', $threadId)
      ->where('m.sender_id = ?', $userId)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($this->select()
      ->union($sql)
      ->order('id ASC')
      ->limit(1)
      );
  }
}