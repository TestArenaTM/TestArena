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
class Application_Model_Message extends Custom_Model_Standard_Abstract implements Custom_Interface_Message
{
  const CATEGORY_MESSAGE      = 1;
  const CATEGORY_NOTIFICATION = 2;
  
  const TYPE_MESSAGE_SENT     = 1;
  const TYPE_MESSAGE_RECEIVED = 2;
  
  protected $_map = array(
    'sender_type'    => 'senderType',
    'recipient_type' => 'recipientType',
    'thread_id'      => 'threadId',
    'to_status'      => 'toStatus',
    'from_status'    => 'fromStatus',
    'create_date'    => 'createDate',
    'received_date'  => 'receivedDate'
  );
  
  private $_id                    = null;
  private $_sender                = null;
  private $_senderType            = null;
  private $_recipient             = null;
  private $_recipientType         = null;
  private $_threadId              = null;
  private $_status                = null;
  private $_toStatus              = null;
  private $_fromStatus            = null;
  private $_createDate            = null;
  private $_receivedDate          = null;
  private $_subject               = null;
  private $_content               = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getSenderId()
  {
    return $this->getSender()->getId();
  }
  
  public function getSender()
  {
    return $this->_sender;
  }
  
  public function getSenderType()
  {
    return $this->_senderType;
  }
  
  public function getSenderTypeId()
  {
    return $this->getSenderType()->getId();
  }
  
  public function getRecipientId()
  {
    return $this->getRecipient()->getId();
  }
  
  public function getRecipient()
  {
    return $this->_recipient;
  }
  
  public function getRecipientType()
  {
    return $this->_recipientType;
  }
  
  public function getRecipientTypeId()
  {
    return $this->getRecipientType()->getId();
  }
  
  public function getThreadId()
  {
    return $this->_threadId;
  }
  
  public function getStatus()
  {
    return $this->_status;
  }
  
  public function getStatusId()
  {
    return $this->getStatus()->getId();
  }
  
  public function getToStatus()
  {
    return $this->_toStatus;
  }
  
  public function getToStatusId()
  {
    return $this->getToStatus()->getId();
  }
  
  public function getFromStatus()
  {
    return $this->_fromStatus;
  }
  
  public function getFromStatusId()
  {
    return $this->getFromStatus()->getId();
  }
  
  public function getCreateDate()
  {
    return $this->_createDate;
  }
  
  public function getReceivedDate()
  {
    return $this->_receivedDate;
  }

  public function getSubject()
  {
    return $this->_subject;
  }

  public function getContent()
  {
    return $this->_content;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }
  
  public function setSenderUser($propertyName, $propertyValue)
  {
    if (null === $this->getSender())
    {
      $this->_sender = new Application_Model_User(array($propertyName => $propertyValue));
      $this->setSenderType(Application_Model_MessageUserType::USER);
    }
    else
    {
      if ($this->getSenderTypeId() == Application_Model_MessageUserType::USER)
      {
        $this->getSender()->setProperty($propertyName, $propertyValue);
      }
    }
    
    return $this;
  }
  
  public function setSenderCoach($propertyName, $propertyValue)
  {
    if (null === $this->getSender())
    {
      $this->_sender = new Application_Model_Coach(array($propertyName => $propertyValue));
      $this->getSender()->setType(Application_Model_AdminType::COACH);
      $this->setSenderType(Application_Model_MessageUserType::COACH);
    }
    else
    {
      if ($this->getSenderTypeId() == Application_Model_MessageUserType::COACH)
      {
        $this->getSender()->setProperty($propertyName, $propertyValue);
      }
    }
    
    return $this;
  }
  
  public function setSenderObject(Custom_Interface_ApplicationUser $sender)
  {
    $this->_sender = $sender;
    
    if ($sender instanceof Application_Model_User)
    {
      $this->setSenderType(Application_Model_MessageUserType::USER);
    }
    else
    {
      $this->setSenderType(Application_Model_MessageUserType::COACH);
    }
    
    return $this;
  }
  
  public function setSenderType($id)
  {
    $this->_senderType = new Application_Model_MessageUserType($id);
    return $this;
  }
  
  public function setRecipientUser($propertyName, $propertyValue)
  {
    if (null === $this->getRecipient())
    {
      $this->_recipient = new Application_Model_User(array($propertyName => $propertyValue));
      $this->setRecipientType(Application_Model_MessageUserType::USER);
    }
    else
    {
      if ($this->getRecipientTypeId() == Application_Model_MessageUserType::USER)
      {
        $this->getRecipient()->setProperty($propertyName, $propertyValue);
      }
    }
    
    return $this;
  }
  
  public function setRecipientCoach($propertyName, $propertyValue)
  {
    if (null === $this->getRecipient())
    {
      $this->_recipient = new Application_Model_Coach(array($propertyName => $propertyValue));
      $this->getRecipient()->setType(Application_Model_AdminType::COACH);
      $this->setRecipientType(Application_Model_MessageUserType::COACH);
    }
    else
    {
      if ($this->getRecipientTypeId() == Application_Model_MessageUserType::COACH)
      {
        $this->getRecipient()->setProperty($propertyName, $propertyValue);
      }
    }
    
    return $this;
  }
  
  public function setRecipientObject(Custom_Interface_ApplicationUser $recipient)
  {
    $this->_recipient = $recipient;
    
    if ($recipient instanceof Application_Model_User)
    {
      $this->setRecipientType(Application_Model_MessageUserType::USER);
    }
    else
    {
      $this->setRecipientType(Application_Model_MessageUserType::COACH);
    }
    
    return $this;
  }
  
  public function setRecipientType($id)
  {
    $this->_recipientType = new Application_Model_MessageUserType($id);
    return $this;
  }
  
  public function setThreadId($threadId)
  {
    $this->_threadId = $threadId;
    return $this;
  }
  
  public function setStatus($id)
  {
    $this->_status = new Application_Model_MessageStatus($id);
    return $this;
  }
  
  public function setToStatus($id)
  {
    $this->_toStatus = new Application_Model_MessageToStatus($id);
    return $this;
  }
  
  public function setFromStatus($id)
  {
    $this->_fromStatus = new Application_Model_MessageFromStatus($id);
    return $this;
  }
  
  public function setCreateDate($date)
  {
    $this->_createDate = $date;
    return $this;
  }
  
  public function setReceivedDate($date)
  {
    $this->_receivedDate = $date;
    return $this;
  }
  
  public function setSubject($subject)
  {
    $this->_subject = $subject;
    return $this;
  }

  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }
  // </editor-fold>
  
  public function __toString()
  {
    return __CLASS__;
  }
}