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
class Project_IndexController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    
    if (!$this->getRequest()->isXmlHttpRequest())
    {
      if ($this->_project === null)
      {
        throw new Custom_404Exception();
      }
      
      $this->checkUserSession(true);
      
      if (!in_array($this->getRequest()->getActionName(), array('view')))
      {
        $this->_project->checkFinished();
        $this->_project->checkSuspended();
      }
    }
  }
  
  public function userListAjaxAction()
  {
    $this->checkUserSession(true, true);
    $request = $this->getRequest();
    
    $userMapper = new Project_Model_UserMapper();
    $result     = $userMapper->getAllAjax($request);
      
    echo json_encode($result);
    exit;
  }
    
  public function viewAction()
  {
    $this->_setCurrentBackUrl('file_dwonload');
    $roleMapper = new Project_Model_RoleMapper();
    $attachmentMapper = new Project_Model_AttachmentMapper();

    $this->_setTranslateTitle();
    $this->view->roles = $roleMapper->getListByProjectId($this->_project);
    $this->view->attachments = $attachmentMapper->getForProject($this->_project);
    $this->view->addAttachmentAccess = $this->_checkAccess(Application_Model_RoleAction::PROJECT_ATTACHMENT);
  }
  
  public function addPlanAjaxAction()
  {
    $result = array(
      'status' => 'ERROR',
      'errors' => array()
    );
    
    $this->checkUserSession(true, true);
    $this->_checkAccess(Application_Model_RoleAction::PROJECT_ATTACHMENT, true);
    $ids = explode('_', $this->getRequest()->getPost('ids', ''));
    
    if (count($ids))
    {
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachment = new Application_Model_Attachment();
      $attachment->setSubjectId($this->_project->getId());
      $attachment->setType(Application_Model_AttachmentType::PROJECT_PLAN);

      foreach ($ids as $id)
      {
        $attachment->setFile('id', $id);
        
        if ($attachmentMapper->save4Project($attachment) === false)
        {
          $result['status'] = 'ERROR';
          $result['errors'][] = 'Can not add attachment!';
        }
      }
      
      if (count($result['errors']) == 0)
      {
        $result['status'] = 'SUCCESS';
      }
    }
    else
    {
      $result['errors'][] = 'Not set ids!';
    }
      
    echo json_encode($result);
    exit;
  }
  
  public function addDocumentationAjaxAction()
  {
    $result = array(
      'status' => 'ERROR',
      'errors' => array()
    );
    
    $this->checkUserSession(true, true);
    $this->_checkAccess(Application_Model_RoleAction::PROJECT_ATTACHMENT, true);
    $ids = explode('_', $this->getRequest()->getPost('ids', ''));
    
    if (count($ids))
    {
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachment = new Application_Model_Attachment();
      $attachment->setSubjectId($this->_project->getId());
      $attachment->setType(Application_Model_AttachmentType::DOCUMENTATION);

      foreach ($ids as $id)
      {
        $attachment->setFile('id', $id);
        
        if ($attachmentMapper->save4Project($attachment) === false)
        {
          $result['status'] = 'ERROR';
          $result['errors'][] = 'Can not add attachment!';
        }
      }
      
      if (count($result['errors']) == 0)
      {
        $result['status'] = 'SUCCESS';
      }
    }
    else
    {
      $result['errors'][] = 'Not set ids!';
    }
      
    echo json_encode($result);
    exit;
  }
  
  public function deleteAttachmentAjaxAction()
  {
    $result = array(
      'status' => 'ERROR',
      'errors' => array()
    );
    
    $this->checkUserSession(true, true);
    $this->_checkAccess(Application_Model_RoleAction::PROJECT_ATTACHMENT, true);
    $id = $this->getRequest()->getParam('id', 0);
    
    if ($id > 0)
    {
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachment = new Application_Model_Attachment();
      $attachment->setId($id);
      $attachmentMapper->delete($attachment);
      $result['status'] = 'SUCCESS';
    }
    else
    {
      $result['errors'][] = 'Not set id!';
    }
      
    echo json_encode($result);
    exit;
  }
}