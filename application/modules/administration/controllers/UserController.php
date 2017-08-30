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
class Administration_UserController extends Custom_Controller_Action_Administration_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
     
    if (!$this->_user->getAdministrator())
    {
      throw new Custom_AccessDeniedException();
    }    
    
    $this->_helper->layout->setLayout('administration');
  }
  
  private function _getFilterForm()
  {
    return new Administration_Form_UserFilter(array('action' => $this->_url(array(), 'admin_user_list')));
  }
  
  public function indexAction()
  {
    $request = $this->getRequest();
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $userMapper = new Administration_Model_UserMapper();
      list($list, $paginator) = $userMapper->getAll($request);
    }
    else
    {
      $list = array();
      $paginator = null;
    }
    
    $this->_setTranslateTitle();
    $this->view->users = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
  }
  
  private function _getAddUserForm()
  {
    return new Administration_Form_AddUser(array(
      'action' => $this->_url(array(), 'admin_user_add_process'),
      'method' => 'post',
    ));
  }
  
  public function addAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddUserForm();
  }

  public function addProcessAction()
  {
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'admin_user_add');
    }
    
    $form = $this->_getAddUserForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $user       = new Application_Model_User($form->getValues(), false);
    $userMapper = new Administration_Model_UserMapper();
    $user->setStatus($form->getValue('activeUser') ? Application_Model_UserStatus::ACTIVE : Application_Model_UserStatus::INACTIVE);
    $t = new Custom_Translate();
    
    if ($userMapper->add($user) && $this->_sendCreatePasswordEmail($user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      $userMapper->deleteByToken($user);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _sendCreatePasswordEmail(Application_Model_User $user)
  {
    $name = 'create-password';
    $html = new Zend_View();
    $html->addHelperPath(Zend_Registry::get('config')->resources->view->helperPath->Application_View_Helper);
    $html->setScriptPath(APPLICATION_PATH . '/modules/administration/views/emails/');

    $html->assign('user', $user);
    $html->assign('name', $name);
    
    $mailBody  = $html->render($name.'.phtml');
    $t = new Custom_Translate();
    $mailTitle = $t->translate('subject', null, 'email_'.$name);

    return Custom_Mail_SendMail::sendmail($mailTitle, $mailBody, $user->getEmail(), $user->getFullname());
  }
  
  private function _getEditUserForm(Application_Model_User $user)
  {
    $form = new Administration_Form_EditUser(array(
      'action'  => $this->_url(array('id' => $user->getId()), 'admin_user_edit_process'),
      'method'  => 'post',
      'id'      => $user->getId()
    ));
    
    $userMapper = new Administration_Model_UserMapper();
    $row = $userMapper->getForEdit($user);
    $row['activeUser'] = $row['status'] == Application_Model_UserStatus::ACTIVE;
    $form->populate($row);
    return $form;
  }
  
  public function editAction()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      return $this->redirect(array(), 'user_add');
    }
    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditUserForm(new Application_Model_User($idValidator->getFilteredValues()));
  }

  public function editProcessAction()
  {
    $request = $this->getRequest();
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$request->isPost() || !$idValidator->isValid($request->getParams()))
    {
      return $this->redirect(array(), 'user_add');
    }
    
    $user = new Application_Model_User($idValidator->getFilteredValues());
    $form = $this->_getEditUserForm($user);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit');
    }
    
    $t = new Custom_Translate();
    $userMapper = new Administration_Model_UserMapper();
    $user->setProperties($form->getValues(), false);
    $user->setStatus($form->getValue('activeUser') ? Application_Model_UserStatus::ACTIVE : Application_Model_UserStatus::INACTIVE);
    
    if ($userMapper->edit($user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  private function _getValidUserForStateAction()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      return $this->redirect(array(), 'user_list');
    }
    
    return new Application_Model_User($idValidator->getFilteredValues());
  }
  
  public function activateAction()
  {
    $user = $this->_getValidUserForStateAction();
    $userMapper = new Administration_Model_UserMapper();
    $t = new Custom_Translate();
    
    if ($userMapper->activate($user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  public function deactivateAction()
  {
    $user = $this->_getValidUserForStateAction();
    $userMapper = new Administration_Model_UserMapper();
    $t = new Custom_Translate();
    
    if ($userMapper->deactivate($user))//TODO WYsłać e-maila do liderów projektów, do których należał użytkownik.
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER')); 
  }
    
  public function resetPasswordAction()
  {
    $user = $this->_getValidUserForStateAction();
    $userMapper = new Administration_Model_UserMapper();
    $t = new Custom_Translate();
    
    if ($userMapper->resetPassword($user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
  
  public function listAjaxAction()
  {
    $userMapper = new Administration_Model_UserMapper();
    $result     = $userMapper->getAllAjax($this->getRequest());
      
    echo json_encode($result);
    exit;
  }
}