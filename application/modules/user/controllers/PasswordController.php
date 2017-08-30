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
class User_PasswordController extends Custom_Controller_Action_Application_Abstract
{
  public function preDispatch()
  {
    $this->checkUserSession();
  }
  
  private function _getCreatePasswordForm()
  {
    return new User_Form_NewPassword(array(
      'action' => $this->_url(array(), 'user_create_password_process'),
      'method' => 'post',
    ));
  }
  
  public function createAction()
  {
    $activateValidator = new User_Model_Validator_Activate();
    
    if ($activateValidator->isValid($this->_getAllParams()))
    {
      $user       = new Application_Model_User($activateValidator->getFilteredValues());
      $userMapper = new User_Model_UserMapper();
      
      if (!$userMapper->getIdByTokenEmail($user))
      {
        $t = new Custom_Translate();
        $this->_messageBox->set($t->translate('statusFaliure'), Custom_MessageBox::TYPE_ERROR);
        $this->redirect(array(), 'index');
      }

      $session = new Zend_Session_Namespace('userSession');
      $session->changePasswordId = $user->getId();
      $session->changePasswordEmail = $user->getEmail();

      $this->_helper->layout->setLayout('not-logged');
      $this->_setTranslateTitle();
      $this->view->form = $this->_getCreatePasswordForm();
    }
    else
    {
      $this->redirect(array(), 'index');
    }
  }
  
  public function createProcessAction()
  {
    $request = $this->getRequest();
    $session = new Zend_Session_Namespace('userSession');

    if (!$request->isPost() || !isset($session->changePasswordId) || !isset($session->changePasswordEmail))
    {
      return $this->redirect(array(), 'index');
    }
    
    $form = $this->_getCreatePasswordForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_helper->layout->setLayout('not-logged');
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('create'); 
    }
    
    $t = new Custom_Translate();
    $userMapper  = new User_Model_UserMapper();
    $user = new Application_Model_User();
    $user->setPassword($form->getValue('password'));
    $user->setId($session->changePasswordId);
    $user->setEmail($session->changePasswordEmail);
    unset($session->changePasswordId, $session->changePasswordEmail);
    
    if ($userMapper->changePassword($user))
    {      
      $userMapper->setLastLoginDate($user);
      $storage = Zend_Auth::getInstance()->getStorage();
      $storage->write($user->getEmail());
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect(array(), 'index');
  }
  
  private function _getResetPasswordForm()
  {
    return new User_Form_NewPassword(array(
      'action' => $this->_url(array(), 'user_reset_password_process'),
      'method' => 'post',
    ));
  }
  
  public function resetAction()
  {
    $this->checkUserSession(true);
    $this->_setTranslateTitle();
    $this->view->form = $this->_getResetPasswordForm();
  }
  
  public function resetProcessAction()
  {
    $this->checkUserSession(true);
    $session = new Zend_Session_Namespace('userSession');
    $request = $this->getRequest();
    
    if (!$request->isPost() || !isset($session->backUrl))
    {
      return $this->redirect(array(), 'user_reset_password');
    }
    
    $form = $this->_getResetPasswordForm();

    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();    
      $this->view->form = $form;
      return $this->render('reset'); 
    }
    
    $userMapper = new User_Model_UserMapper();
    $user = new Application_Model_User();
    $user->setId($this->_user->getId());
    $user->setPassword($form->getValue('password'));
    
    $t = new Custom_Translate();
    
    if ($userMapper->changePassword($user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      $backUrl = $session->backUrl;
      unset($session->backUrl);
      $this->redirect($backUrl);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      return $this->redirect(array(), 'user_reset_password');
    }
  }
  
  private function _getRecoverPassowrdForm()
  {
    return new User_Form_RecoverPassword(array(
      'action' => $this->_url(array(), 'user_recover_password_process'),
      'method' => 'post'
   ));
  }
  
  public function recoverAction()
  {
    $this->_helper->layout->setLayout('not-logged');
    $this->_setTranslateTitle();    
    $this->view->form = $this->_getRecoverPassowrdForm();    
  }
  
  public function recoverProcessAction()
  {
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'user_recover_password');
    }
    
    $form = $this->_getRecoverPassowrdForm();
    
    if (!$form->isValid(array_merge($request->getPost(), array('grecaptcharesponse' => $request->getPost('g-recaptcha-response')))))
    {
      $form->getElement('grecaptcharesponse')->setValue(null);
      $this->_helper->layout->setLayout('not-logged');
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('recover'); 
    }
    
    $userMapper = new User_Model_UserMapper();
    $user = new Application_Model_User();
    $user->setEmail($form->getValue('email'));
    $t = new Custom_Translate();
    
    if ($userMapper->getForRecoverPassword($user))
    {
      if ($userMapper->setNewTokenById($user) && $this->_sendRecoverPassword($user))
      {
        $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      }
      else
      {
        $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      }
      return $this->redirect(array(), 'user_login');
    }
    else
    {
      $this->_messageBox->set($t->translate('statusNotFound'), Custom_MessageBox::TYPE_ERROR);
      return $this->redirect(array(), 'user_recover_password');
    }
  }
  
  private function _sendRecoverPassword(Application_Model_User $user)
  {
    $name = 'recover-password';
    $html = new Zend_View();
    $html->addHelperPath(Zend_Registry::get('config')->resources->view->helperPath->Application_View_Helper);
    $html->setScriptPath(APPLICATION_PATH . '/modules/user/views/emails/');

    $html->assign('user', $user);
    $html->assign('name', $name);

    $mailBody  = $html->render($name.'.phtml');
    $t = new Custom_Translate();
    $mailTitle = $t->translate('subject', null, 'email_'.$name);

    return Custom_Mail_SendMail::sendmail($mailTitle, $mailBody, $user->getEmail(), $user->getFullname());
  }
  
  private function _getNewPassowrdForm()
  {
    return new User_Form_NewPassword(array(
      'action' => $this->_url(array(), 'user_new_password_process'),
      'method' => 'post',
   ));
  }
  
  public function newAction()
  {
    $activateValidator = new User_Model_Validator_Activate();
    
    if ($activateValidator->isValid($this->_getAllParams()))
    {
      $user       = new Application_Model_User($activateValidator->getFilteredValues());

      $userMapper = new User_Model_UserMapper();
      
      if (!$userMapper->getIdByTokenEmail($user))
      {
        $t = new Custom_Translate();
        $this->_messageBox->set($t->translate('statusFaliure'), Custom_MessageBox::TYPE_ERROR);
        $this->redirect(array(), 'index');
      }

      $session = new Zend_Session_Namespace('userSession');
      $session->changePasswordId = $user->getId();
      $session->changePasswordEmail = $user->getEmail();
      
      $this->_helper->layout->setLayout('not-logged');
      $this->_setTranslateTitle();
      $this->view->form = $this->_getNewPassowrdForm();
    }
    else
    {
      $this->redirect(array(), 'user_recover_password');
    }
  }
  
  public function newProcessAction()
  {
    $request = $this->getRequest();
    $session = new Zend_Session_Namespace('userSession');

    if (!$request->isPost() || !isset($session->changePasswordId) || !isset($session->changePasswordEmail))
    {
      return $this->redirect(array(), 'user_recover_password');
    }
    
    $form = $this->_getNewPassowrdForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_helper->layout->setLayout('not-logged');
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('new'); 
    }
    
    $t = new Custom_Translate();
    $userMapper  = new User_Model_UserMapper();
    $user = new Application_Model_User();
    $user->setPassword($form->getValue('password'));
    $user->setId($session->changePasswordId);
    $user->setEmail($session->changePasswordEmail);
    unset($session->changePasswordId, $session->changePasswordEmail);
    
    if ($userMapper->changePassword($user))
    {
      $userMapper->setLastLoginDate($user);
      $storage = Zend_Auth::getInstance()->getStorage();
      $storage->write($user->getEmail());
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect(array(), 'index');
  }
  
  private function _getChangePassowrdForm()
  {
    return new User_Form_ChangePassword(array(
      'action' => $this->_url(array(), 'user_change_password_process'),
      'method' => 'post',
      'email'   => $this->_user->getEmail()
   ));
  }
  
  public function changeAction()
  {
    $this->checkUserSession(true);    
    $this->_setTranslateTitle();
    $this->view->form = $this->_getChangePassowrdForm();
  }
  
  public function changeProcessAction()
  {
    $this->checkUserSession(true);
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'user_change_password');
    }
    
    $form = $this->_getChangePassowrdForm();

    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();    
      $this->view->form = $form;
      return $this->render('change'); 
    }
    
    $userMapper = new User_Model_UserMapper();
    $user = new Application_Model_User();
    $user->setId($this->_user->getId());
    $user->setPassword($form->getValue('password'));
    
    $t = new Custom_Translate();
    
    if ($userMapper->changePassword($user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      $this->_user->setPassword($user->getPassword());
      $this->_user->setSalt($user->getSalt());
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect($form->getBackUrl());
  }
}