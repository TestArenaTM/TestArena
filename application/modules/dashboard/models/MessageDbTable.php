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
class Dashboard_Model_MessageDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'message';
  
  public function getLimitLatest($userId, $limit = 5)
  {
    $sql = $this->select()
      ->from(array('m' => $this->_name), array(
        'id',
        'thread_id',
        'to_status',
        'create_date',
        'subject',
        'content',
        'message_type' => new Zend_Db_Expr(Application_Model_Message::TYPE_MESSAGE_RECEIVED)
      ))
      ->join(array('u' => 'user'), 'm.sender_id = u.id', $this->_createAlias('senderUser', array(
          'id',
          'firstname',
          'lastname',
          'email'
        ))
      )
      ->where('m.recipient_id = ?', $userId)
      ->where('m.from_status = ?', Application_Model_MessageFromStatus::SENT)
      ->where('m.recipient_type = ?', Application_Model_MessageUserType::USER)
      ->order('create_date DESC')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($this->select()
      ->from($sql,
        array(
          'id' => new Zend_Db_Expr('max(id)'),
          'thread_id',
          'to_status' => new Zend_Db_Expr('min(to_status)'),
          'create_date' => new Zend_Db_Expr('max(create_date)'),
          'subject',
          'content',
          'message_type',
          'senderUser$id',
          'senderUser$firstname',
          'senderUser$lastname',
          'senderUser$email'
        ))
      ->group('thread_id')
      ->order('create_date DESC')
      ->limit($limit)
      ->setIntegrityCheck(false));
  }
  
  public function getNumberOfUnread($userId)
  {
    $sql = $this->select()
      ->from(array('m' => $this->_name), array(
        'thread_id'
      ))
      ->where('m.recipient_id = ?', $userId)
      ->where('m.to_status = ?', Application_Model_MessageToStatus::UNREAD)
      ->where('m.from_status = ?', Application_Model_MessageFromStatus::SENT)
      ->group('m.thread_id');
    
    return $this->getAdapter()->fetchOne($this->select()
              ->from($sql, array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => 'COUNT(DISTINCT thread_id)'))
              ->setIntegrityCheck(false));
  }
}