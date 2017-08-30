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
class ContactController extends Custom_Controller_Action_Application_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    
    if (!$this->checkUserSession())
    {
      $this->_helper->layout->setLayout('static');
    }
  }
  
  private function _getContactForm()
  {
    $options = array('action' => $this->_url(array(), 'contact_process'));
    
    if ($this->checkUserSession())
    {
      $options['email'] = $this->_user->getEmail();
    }
    
    return $form = new Application_Form_Contact($options);
  }
  
  public function indexAction()
  {
    $this->_setTranslateTitle();
    $this->view->form = $this->_getContactForm();
  }
  
  public function processAction()
  {
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->redirect(array(), 'contact');
    }
    
    $form = $this->_getContactForm();
    if (!$form->isValid(array_merge($request->getPost(), array('grecaptcharesponse' => $request->getPost('g-recaptcha-response')))))
    {
      $form->getElement('grecaptcharesponse')->setValue(null);
      $this->_setTranslateTitle();    
      $this->view->form = $form;
      return $this->render('index');
    }
    
    $t = new Custom_Translate();
    
    if ($this->_sendContactEmail($form->getValues()))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->redirect(array(), 'contact');
  }
  
  private function _sendContactEmail($values)
  {
    $name = 'contact';
    $html = new Zend_View();
    $html->addHelperPath(Zend_Registry::get('config')->resources->view->helperPath->Application_View_Helper);
    $html->setScriptPath(APPLICATION_PATH.'/views/emails/');
    
    $html->assign('values', $values);
    $html->assign('name', $name);
    
    $mailBody = $html->render($name.'.phtml');
    
    return Custom_Mail_SendMail::sendmail($values['subject'], $mailBody, false, false, $values['email'], $values['name']);
  }  
  
  /**public function cronPrepareAction()
  {
    exec('php '._ROOT_DIR.'/cron/cron.php group2',$op);
    echo '<pre>';
    var_dump($op);
    echo '</pre>';
    exit;
  }
  
  public function cronSendAction()
  {
    exec('php '._ROOT_DIR.'/cron/cron.php group3',$op);
    echo '<pre>';
    var_dump($op);
    echo '</pre>';
    exit;
  }*/
}