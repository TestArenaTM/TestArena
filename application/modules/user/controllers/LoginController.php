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
class User_LoginController extends Custom_Controller_Action_Application_Abstract
{  
  public function getAuthAdapter(array $params)
  {
    $dbAdapter   = Zend_Db_Table::getDefaultAdapter();
    $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        
    $authAdapter->setTableName('user')
                ->setIdentityColumn('email')
                ->setCredentialColumn('password')
                ->setCredentialTreatment('SHA1(CONCAT(md5(?),salt))')
                ->setIdentity($params['email'])
                ->setCredential(Application_Model_User::addSaltToPassword($params['password']));
    
    return $authAdapter;
  }
  
  public function preDispatch()
  {
    $request = $this->getRequest();
    
    if ($this->checkUserSession())
    {
      if ('logout' != $request->getActionName())
      {
        $this->redirect(array(), 'index');
      }
    }
    else
    {
      if ('logout' == $request->getActionName())
      {
        $this->redirect(array(), 'user_login');
      }
    }
    
    $this->_helper->layout->setLayout('not-logged');
  }
  
  private function _getForm($formWithCaptcha)
  {
    return new User_Form_Login(array(
      'action'        => $this->_url(array(), 'user_login_process'),
      'method'        => 'post',
      'turnOnCaptcha' => $formWithCaptcha
    ));
  }
  
  public function indexAction()
  {
    $authorizationLog = new User_Model_AuthorizationLogMapper();
    $formWithCaptcha = $authorizationLog->checkFailedLoginByLimit(3, '0:0:30');
    $this->view->form = $this->_getForm($formWithCaptcha);
    return $this->render($formWithCaptcha ? 'index-with-captcha' : 'index');
  }
  
  public function processAction()
  {
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->redirect(array(), 'user_login');
    }

    $authorizationLogMapper = new User_Model_AuthorizationLogMapper();
    $formWithCaptcha = $request->getPost('g-recaptcha-response', null) === null ? $authorizationLogMapper->checkFailedLoginByLimit(2, '0:0:30') : true;
    $form = $this->_getForm($formWithCaptcha);
    $authorizationLog = new Application_Model_AuthorizationLog();
    
    $postData = $request->getPost();
    
    if ($formWithCaptcha)
    {
      $postData = array_merge($postData, array('grecaptcharesponse' => $request->getPost('g-recaptcha-response')));
    }
    
    if (!$form->isValid($postData))
    {
      if ($formWithCaptcha)
      {
        $form->getElement('grecaptcharesponse')->setValue(null);
      }
      
      $authorizationLog->setUsername($form->getValue('email'));
      $authorizationLogMapper->logFailedLogin($authorizationLog);
      $this->_setTranslateTitle();      
      $csrfErrors = $form->getErrors('csrf');
      
      if (count($csrfErrors) == 0 && ($form->getValue('email') != '' || $form->getValue('password') != ''))
      {
        $this->view->formError = 'invalidCredentials';
      }
      
      $this->view->form = $form;
      return $this->render($formWithCaptcha ? 'index-with-captcha' : 'index');
    }
  
    $authorizationLog->setUsername($form->getValue('email'));
    $adapter = $this->getAuthAdapter($form->getValues());
    $auth = Zend_Auth::getInstance();
    $result = $auth->authenticate($adapter);
    
    if (!$result->isValid())
    {
      if ($formWithCaptcha)
      {
        $form->getElement('grecaptcharesponse')->setValue(null);
      }
      
      $authorizationLogMapper->logFailedLogin($authorizationLog);
      $this->_setTranslateTitle();
      $this->view->formError = 'invalidCredentials';
      $this->view->form = $form;
      return $this->render($formWithCaptcha ? 'index-with-captcha' : 'index');
    }
    
    $user = new Application_Model_User(array('email' => Zend_Auth::getInstance()->getIdentity()));

    $userMapper = new User_Model_UserMapper();
    $user = $userMapper->getByEmail($user);

    if ($user->getStatusId() == Application_Model_UserStatus::INACTIVE)
    {
      $this->_setTranslateTitle();
      $this->view->formError = 'userInactive';
      $this->view->form = $form;
      return $this->render($formWithCaptcha ? 'index-with-captcha' : 'index');
    }

    $userMapper->setLastLoginDate($user);

    $authorizationLog->fillUserByUser($user);
    $authorizationLogMapper->logSuccessfulLogin($authorizationLog);
    
    $userRowObject = $adapter->getResultRowObject('id', array('description', 'password', 'salt', 'token'));
    
    if ($form->getValue('remember'))
    {
      $token = $userRowObject->id.'.'.strtotime($user->getLastLoginDate());
		  setcookie('RememberMe', $token, time()+60*60*24*30, '/', Zend_Registry::get('config')->cookie_domain, false, true);
    }
    
    setcookie('FrameProfile', base64_encode($user->getEmail()), 0, '/', Zend_Registry::get('config')->cookie_domain, false, true);
    
    $this->redirect($this->_getBackUrl('user_login', $this->_url(array(), 'index'), true));
  }
  
  public function logoutAction()
  {
    $authorizationLogMapper = new User_Model_AuthorizationLogMapper();
    $authorizationLogMapper->logLogout($this->_user);
    Application_Model_User::clearIdentity();
    $this->redirect(array(), 'user_login');
  }
}