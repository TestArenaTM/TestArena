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
class Message_Model_MessageMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Message_Model_MessageDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setItemCountPerPage(10);
    
    $paginator->setCurrentPageNumber($request->getParam('page', 1));
    
    $list = array();
    
    $firstMsgId = 0;
    $isFirstRow = true;
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      if ($isFirstRow)
      {
        $firstMsgId = $row['thread_id'];
      }
      
      $list[] = new Application_Model_Message($this->prepareMessageRow($row));
      $isFirstRow = false;
    }
    
    return array($list, $paginator, $firstMsgId);
  }
  
  public function setStatus(Application_Model_Message $message)
  {
    $db = $this->_getDbTable();
    
    try
    {
      $data = array(
        'to_status' => Application_Model_MessageToStatus::READ
      );

      $where = array(
        'thread_id = ?'      => $message->getThreadId(),
        'recipient_id = ?'   => $message->getRecipientId(),
        'recipient_type = ?' => Application_Model_MessageUserType::USER,
        'to_status = ?'      => Application_Model_MessageToStatus::UNREAD
      );
      
      $db->update($data, $where);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function add(Application_Model_Message $message)
  {
    $db = $this->_getDbTable();
    
    try
    {
      $data = array(
        'sender_id'      => $message->getSenderId(),
        'sender_type'    => $message->getSenderTypeId(),
        'recipient_id'   => $message->getRecipientId(),
        'recipient_type' => $message->getRecipientTypeId(),
        'status'         => Application_Model_MessageStatus::BASE,
        'to_status'      => Application_Model_MessageToStatus::UNREAD,
        'from_status'    => Application_Model_MessageFromStatus::SENT,
        'create_date'    => date('Y-m-d H:i:s'),
        'subject'        => $message->getSubject(),
        'content'        => $message->getContent()
      );
      
      $message->setId($db->insert($data));
      
      $db->update(array('thread_id' => $message->getId()), array('id = ?' => $message->getId()));
      
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function respond(Application_Model_Message $message, $messageStatus)
  {
    $db = $this->_getDbTable();
    
    try
    {
      $data = array(
        'sender_id'      => $message->getSenderId(),
        'sender_type'    => $message->getSenderTypeId(),
        'recipient_id'   => $message->getRecipientId(),
        'recipient_type' => $message->getRecipientTypeId(),
        'thread_id'      => $message->getThreadId(),
        'status'         => $messageStatus,
        'to_status'      => Application_Model_MessageToStatus::UNREAD,
        'from_status'    => Application_Model_MessageFromStatus::SENT,
        'create_date'    => $message->getCreateDate(),
        'subject'        => $message->getSubject(),
        'content'        => $message->getContent()
      );
      
      $message->setId($db->insert($data));
      
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function getSingleThreadMessageByUser(Application_Model_Message $message, Application_Model_User $user)
  {
    $row = $this->_getDbTable()->getSingleThreadMessageByUser($message->getThreadId(), $user->getId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $message->setDbProperties($this->prepareMessageRow($row->toArray()));
  }
  
  public function getUsersThreadMessagesByThreadAjax($threadId, Application_Model_User $user)
  {
    return $this->_getDbTable()->getUsersThreadMessagesByThreadAjax($threadId, $user->getId())->toArray();
  }
  
  public function checkValidThreadByUser($threadId, Application_Model_User $user)
  {
    return $this->_getDbTable()->checkValidThreadByUser($threadId, $user->getId());
  }
    
  public function prepareMessageRow($row)
  {
    foreach($row as $name => $value)
    {
      if (strpos($name, 'user$') !== false)
      {
        switch ($row['message_type'])
        {
          case Application_Model_Message::TYPE_MESSAGE_RECEIVED:
            $row[str_replace('user$', 'senderUser$', $name)] = $value;
            break;
          case Application_Model_Message::TYPE_MESSAGE_SENT:
            $row[str_replace('user$', 'recipientUser$', $name)] = $value;
            break;
        }
        
        unset($row[$name]);
      }
    }
    
    return $row;
  }
}