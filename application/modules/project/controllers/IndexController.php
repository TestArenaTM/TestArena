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
      
      if (!in_array($this->getRequest()->getActionName(), array('view', 'activate', 'suspend', 'finish')))
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
    $this->view->accessAddAttachment = $this->_checkAccess(Application_Model_RoleAction::PROJECT_ATTACHMENT);
    $this->view->accessProjectStatus = $this->_checkProjectStatusAccess(false);
  }
  
  public function activateAction()
  {
    $this->_checkProjectStatusAccess();
    
    $projectMapper = new Project_Model_ProjectMapper();
    $t = new Custom_Translate();
    
    if ($projectMapper->activate($this->_project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  public function suspendAction()
  {
    $this->_checkProjectStatusAccess();
    
    $projectMapper = new Project_Model_ProjectMapper();
    $t = new Custom_Translate();
    
    if ($projectMapper->suspend($this->_project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER')); 
  }
  
  public function finishAction()
  {
    $this->_checkProjectStatusAccess();
    
    $projectMapper = new Project_Model_ProjectMapper();
    $t = new Custom_Translate();
    
    if ($projectMapper->finish($this->_project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER')); 
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
    $t = new Custom_Translate();
    
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
          $result['errors'][] = $t->translate("attachmentCanNotAdd", null, 'error');
        }
      }
      
      if (count($result['errors']) == 0)
      {
        $result['status'] = 'SUCCESS';
      }
    }
    else
    {
      $result['errors'][] = $t->translate("attachmentNotSetIds", null, 'error');
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
    $t = new Custom_Translate();
    
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
          $result['errors'][] = $t->translate("attachmentCanNotAdd", null, 'error');
        }
      }
      
      if (count($result['errors']) == 0)
      {
        $result['status'] = 'SUCCESS';
      }
    }
    else
    {
      $result['errors'][] = $t->translate("attachmentNotSetIds", null, 'error');
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
      $t = new Custom_Translate();
      $result['errors'][] = $t->translate("attachmentNotSetIds", null, 'error');
    }
      
    echo json_encode($result);
    exit;
  }
  
  private function _checkProjectStatusAccess($throwException = true)
  {
    if ($this->_project->getStatusId() == Application_Model_ProjectStatus::FINISHED
        || !$this->_checkAccess(Application_Model_RoleAction::PROJECT_STATUS))
    {
      if ($throwException)
      {
        $this->_throwTaskAccessDeniedException();
      }
      
      return false;
    }
    
    return true;
  }
}