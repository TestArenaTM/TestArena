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
class User_IndexController extends Custom_Controller_Action_Application_Abstract
{ 
  public function preDispatch()
  {
    parent::preDispatch();
    
    $this->checkUserSession(true);
	}
  
  public function indexAction()
  {
    $roleMapper = new User_Model_RoleMapper();
    list($roleList, $projectList) = $roleMapper->getForUserProfile($this->_user);
    $this->_setTranslateTitle();
    $this->view->roles = $roleList;
    $this->view->projects = $projectList;
  }
  
  private function _getEditForm()
  {
    $form = new User_Form_Edit(array(
      'action'  => $this->_url(array(), 'user_edit_process'),
      'method'  => 'post'
    ));
    
    $form->populate(array(
      'firstname'     => $this->_user->getFirstname(),
      'lastname'      => $this->_user->getLastname(),
      'organization'  => $this->_user->getOrganization(),
      'department'    => $this->_user->getDepartment(),
      'phoneNumber'   => $this->_user->getPhoneNumber()
    ));
    
    return $form;
  }
  
  public function editAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditForm();
  }

  public function editProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'user_profile');
    }
    
    $form = $this->_getEditForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('edit');
    }
    
    $user = new Application_Model_User($form->getValues(), false);
    $user->setId($this->_user->getId());
    $userMapper = new User_Model_UserMapper();
    
    $t = new Custom_Translate();
    
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
  
  private function _getChangeAvatarForm()
  {
    $form = new User_Form_UserAvatar(array(
      'action'                      => $this->_url(array(), 'user_change_avatar_process'),
      'method'                      => 'post',
      'enctype'                     => 'multipart/form-data',
      'avatarDestinationDirectory'  => $this->_user->getAvatarDirectory(Application_Model_User::AVATAR_DIRCTORY_TEMP),
      'avatarFileName'              => $this->_user->getId()
    ));
    
    return $form->setAttrib('id', 'avatar_change');
  }
  
  public function changeAvatarAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getChangeAvatarForm();
  }
  
  public function changeAvatarProcessAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'user_profile');
    }
    
    $form = $this->_getChangeAvatarForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;      
      return $this->render('change-avatar');
    }
    
    $user = new Application_Model_User($form->getValues());
    $user->setId($this->_user->getId());
    $userMapper = new User_Model_UserMapper();
    
    $t = new Custom_Translate();
    
    $status = $userMapper->changeAvatar($user);
    
    if (true === $status)
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      if (!is_bool($status))
      {
        $this->view->errorFile = $status;
        $this->view->form = $form;
        $this->view->user = $user;
        return $this->render('change-avatar');
      }
      
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
  
  public function deleteAvatarAction()
  {
    $userMapper = new User_Model_UserMapper();
    $t = new Custom_Translate();
    
    if ($userMapper->deleteAvatar($this->_user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($this->getRequest()->getServer('HTTP_REFERER'));
  }
}