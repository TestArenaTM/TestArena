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
class User_EmailController extends Custom_Controller_Action_Application_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
	}
  
  private function _getChangeEmailForm()
  {
    return new User_Form_ChangeEmail( array(
      'action'  => $this->_url(array(), 'user_change_email_process'),
      'method'  => 'post',
      'email'   => $this->_user->getEmail()
    ));
  }
  
  public function indexAction()
  {
    $this->checkUserSession(true);
    $this->_setTranslateTitle();
    $this->view->form = $this->_getChangeEmailForm();
  }
  
  public function processAction()
  {
    $this->checkUserSession(true);
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'user_change_email');
    }
    
    $form = $this->_getChangeEmailForm();
     
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('index'); 
    }
    
    $userMapper = new User_Model_UserMapper();
    $this->_user->setNewEmail($form->getValue('newEmail'));
    
    $t = new Custom_Translate();
    $userMapper->setNewEmail($this->_user);
    
    if ($this->_sendActivateNewEmail($this->_user))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }

    $this->redirect($form->getBackUrl());
  }
  
  private function _sendActivateNewEmail(Application_Model_User $user)
  {
    $name = 'activate-new-email';
    $html = new Zend_View();
    $html->addHelperPath(Zend_Registry::get('config')->resources->view->helperPath->Application_View_Helper);
    $html->setScriptPath(APPLICATION_PATH . '/modules/user/views/emails/');

    $html->assign('user', $user);
    $html->assign('name', $name);

    $mailBody  = $html->render($name.'.phtml');
    $t = new Custom_Translate();
    $mailTitle = $t->translate('subject', null, 'email_'.$name);

    return Custom_Mail_SendMail::sendmail($mailTitle, $mailBody, $user->getNewEmail());
  }
  
  public function activateAction()
  {
    $activateValidator = new User_Model_Validator_Activate();
    
    if ($activateValidator->isValid($this->_getAllParams()))
    {
      $user       = new Application_Model_User($activateValidator->getFilteredValues());
      $userMapper = new User_Model_UserMapper();
      
      $t = new Custom_Translate();
      
      if ($userMapper->getNewEmailByTokenEmail($user) && $userMapper->changeEmail($user))
      {
        $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);

        if ($this->checkUserSession())
        {
          $storage = Zend_Auth::getInstance()->getStorage();
          $storage->write($user->getNewEmail());
          $this->_user->setEmail($user->getNewEmail());
          return $this->redirect(array(), 'index');
        }
        
        return $this->redirect(array(), 'user_login');
      }
    }
    
    $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);

    if ($this->checkUserSession())
    {
      $this->redirect(array(), 'index');
    }
    else
    {
      $this->redirect(array(), 'user_login');
    }
  }
}