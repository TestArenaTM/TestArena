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
class Message_IndexController extends Custom_Controller_Action_Application_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    if (!$this->getRequest()->isXmlHttpRequest())
    {
      $this->checkUserSession(true);
    }
  }
  
  public function indexAction()
  {
    $this->_setTranslateTitle();
    $this->getHelper('HTMLPurifier')->run();
    
    $request = $this->getRequest();
    $request->setParam('user_id', $this->_user->getId());
    
    $messageMapper = new Message_Model_MessageMapper();
    
    list($list, $paginator, $firstMsgId) = $messageMapper->getAll($request);
    
    $this->view->request = $request;
    $this->view->messages = $list;
    $this->view->paginator = $paginator;
    $this->view->messageItemId = ($request->getParam('item_id', 0) > 0) ? $request->getParam('item_id') : $firstMsgId;
    $this->view->messageItemType = $request->getParam('item_type');
  }
  
  public function threadListAjaxAction()
  {
    $this->checkUserSession(true, true);
    $request        = $this->getRequest();
    $threadMessages = array();
    
    if ($this->getRequest()->isXmlHttpRequest() && $request->isPost())
    {
      $validThreadId = $this->_checkValidThreadId();
      
      $messageMapper  = new Message_Model_MessageMapper();
      $threadMessages = $messageMapper->getUsersThreadMessagesByThreadAjax($validThreadId, $this->_user);
    }
    
    echo json_encode($this->_prepareThreadMessagesRows($threadMessages));
    exit;
  }
  
  public function userListAjaxForMessageAction()
  {
    $request = $this->getRequest();
    $request->setParam('currentUserId', $this->_user->getId());
    
    $userMapper = new User_Model_UserMapper();
    $result     = $userMapper->getAllAjaxForMessage($request);
      
    echo json_encode($result);
    exit;
  }
  
  public function addAction()
  {
    $this->_setTranslateTitle();
    
    $form = $this->_getAddForm();
    $this->view->form = $form;
  }
  
  public function addProcessAction()
  {
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'message_add');
    }
    
    $form = $this->_getAddForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $message = new Application_Model_Message($form->getValues(), false);
    $message->setSenderObject($this->_user);
    $message->setRecipientUser('id', $form->getValue('userId'));
    
    $messageMapper = new Message_Model_MessageMapper();
    
    $t = new Custom_Translate();
    
    if ($messageMapper->add($message))
    {
      $this->_messageBox->set($t->translate('statusAddSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusAddError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect(array(), 'message_list');
  }
  
  public function responseAjaxAction()
  {
    $this->checkUserSession(true, true);
    $request = $this->getRequest();
    
    if ($this->getRequest()->isXmlHttpRequest() && $request->isPost())
    {
      $message = $this->_getValidSingleThreadMessage();
      
      $form = $this->_getResponseForm();
      
      if (!$form->isValid($request->getPost()))
      {
        echo json_encode(-1);
        exit;
      }
      
      $interlocutorId = ($message->getExtraData('message_type') == Application_Model_Message::TYPE_MESSAGE_SENT) ?
                        $message->getRecipientId():
                        $message->getSenderId();
    
      $responseMessage = new Application_Model_Message($form->getValues(), false);
      $responseMessage->setSenderType(Application_Model_MessageUserType::USER);
      $responseMessage->setSenderObject($this->_user);
      $responseMessage->setRecipientType(Application_Model_MessageUserType::USER);
      $responseMessage->setRecipientUser('id', $interlocutorId);
      $responseMessage->setSubject($message->getSubject());
      $responseMessage->setThreadId($message->getThreadId());
      $responseMessage->setCreateDate(date('Y-m-d H:i:s'));
      
      $messageMapper = new Message_Model_MessageMapper();
      
      if ($messageMapper->respond($responseMessage, Application_Model_MessageStatus::REPLIED))
      {
        $purifier = $this->getHelper('HTMLPurifier')->run();
        $timeAgo  = $this->view->getHelper('timeAgo');
        
        $responseData = array(
          'userFullname'  => $this->_user->getFullname(),
          'userAvatarUrl' => $this->_user->getAvatarUrl(true),
          'msgContent'    => nl2br($purifier->purify($responseMessage->getContent())),
          'msgCreateDate' => $timeAgo->timeAgo($responseMessage->getCreateDate()),
          'messageType'   => $message->getExtraData('message_type')
        );
        
        echo json_encode($responseData);
        exit;
      }
      
      echo json_encode(0);
      exit;
    }
  }
  
  public function readAjaxAction()
  {
    $this->checkUserSession(true, true);
    
    $request = $this->getRequest();
    $status  = false;
    
    if (($request->getPost('itemId', 0) > 0)
         && ($request->getPost('itemType', 0) > 0))
    {
      list($itemId, $messageCategory) = array($request->getPost('itemId'), $request->getPost('itemType'));
      
      switch (true)
      {
        case Application_Model_Message::CATEGORY_NOTIFICATION == $messageCategory:
          $notificationUser = new Application_Model_NotificationUser();
          
          $notificationUser->setNotification('id', $itemId);
          $notificationUser->setUserObject($this->_user);
          $notificationUser->setStatus(Application_Model_NotificationUserStatus::READ);
          $notificationUserMapper = new Application_Model_NotificationUserMapper();

          if ($notificationUserMapper->setStatus($notificationUser))
          {
            $status = true;
          }
          break;
        case Application_Model_Message::CATEGORY_MESSAGE == $messageCategory:
          $message = new Application_Model_Message();
          
          $message->setThreadId($itemId);
          $message->setRecipientObject($this->_user);
          $messageMapper = new Message_Model_MessageMapper();

          if ($messageMapper->setStatus($message))
          {
            $status = true;
          }
          break;
      }
    }
    
    echo $status;
    exit;
  }
  
  private function _checkValidThreadId()
  {
    $validThreadId = $this->_getValidThreadId();
    $messageMapper = new Message_Model_MessageMapper();
    
    if (!$messageMapper->checkValidThreadByUser($validThreadId, $this->_user))
    {
      $this->_throwMessageNotFoundException();
    }
    
    return $validThreadId;
  }
  
  private function _getValidThreadId()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      return $this->redirect(array(), 'message_list');
    }
    
    return array('thread_id' => $idValidator->getFilteredValue('id'));
  }
  
  private function _throwMessageNotFoundException()
  {
    if ($this->getRequest()->isXmlHttpRequest())
    {
      echo json_encode(0);
      exit;
    }
    else
    {
      throw new Custom_404Exception('Message not found!');
    }
  }
  
  private function _prepareThreadMessagesRows(array $threadMessages)
  {
    $result        = array();
    $messageMapper = new Message_Model_MessageMapper();
    $purifier      = $this->getHelper('HTMLPurifier')->run();
    $timeAgo       = $this->view->getHelper('timeAgo');
    
    if (count($threadMessages) < 1)
    {
      return $result;
    }
    
    foreach ($threadMessages as $messageRow)
    {
      $message = new Application_Model_Message($messageMapper->prepareMessageRow($messageRow));
      $sender  = (Application_Model_Message::TYPE_MESSAGE_RECEIVED == $message->getExtraData('message_type')) ?
                                                                      $message->getSender() :
                                                                      $this->_user;
      
      $result[] = array(
        'msgThreadId'   => $message->getThreadId(),
        'userFullname'  => $sender->getFullname(),
        'userAvatarUrl' => $sender->getAvatarUrl(true),
        'msgContent'    => nl2br($purifier->purify($message->getContent())),
        'msgCreateDate' => $timeAgo->timeAgo($message->getCreateDate()),
        'messageType'   => $message->getExtraData('message_type')
      );
    }
    
    return $result;
  }
  
  private function _getAddForm()
  {
    return new Message_Form_Add(array(
      'action'  => $this->_url(array(), 'message_add_process'),
      'method'  => 'post'
    ));
  }
  
  private function _getValidSingleThreadMessage()
  {
    $messageMapper = new Message_Model_MessageMapper();
    $message = $messageMapper->getSingleThreadMessageByUser(new Application_Model_Message($this->_getValidThreadId()), $this->_user);
    
    if (!$message)
    {
      $this->_throwMessageNotFoundException();
    }
    
    return $message;
  }
  
  private function _getResponseForm()
  {
    return new Message_Form_Response(array(
      'method'  => 'post'
    ));
  }
}