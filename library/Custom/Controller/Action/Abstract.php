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
abstract class Custom_Controller_Action_Abstract extends Zend_Controller_Action
{
  const TITLE_ATTACH_ORDER_PREPEND = 'PREPEND';
  const TITLE_ATTACH_ORDER_APPEND = 'APPEND';
  const TITLE_ATTACH_ORDER_SET = 'SET';
  
  const STATUS_USER_NOT_LOGGED = 'STATUS_USER_NOT_LOGGED';
  
  protected $_user       = null;
  protected $_messageBox = null;
  
  public function init()
  {
    parent::init();
    $this->_rememberMeAutologin();
    $this->_setLoggedUser();
    $this->_initLocale();
    $this->_initMessageBox();
    $this->_initViewParams();
    $this->_setDefaultTitle();
    //$this->_initGeneralBackUrl();
    Custom_CookieLaw::init();
    $this->_initActiveMenu();
    
    if (!$this->getRequest()->isXmlHttpRequest())
    {
      if ($this->checkUserSession())
      {
        $this->_checkResetPassword();
      }
    }
  }
  
  public function redirect($action = 'index', $controller = 'index', $module = 'default', $params = array(), $route = null, $reset = true)
  {
    $this->_redirect = $this->_helper->getHelper('Redirector');
    
    if ( is_array($action) )
    {
      return $this->_redirect->setExit(true)
    	                       ->gotoRoute($action, $controller, $reset);
    }

    if ( strstr($action, 'http') )
    {
    	return $this->_redirect($action, array('code' => 301));
    }
    
    if ( $route !== null )
    {
    	$params = array_merge(array('action'     => $action,
              					    	    'controller' => $controller,
                             			'module'     => $module), $params);

    	return $this->_redirect->setCode(301)
    	                       ->setExit(true)
    	                       ->gotoRoute($params, $route, $reset);
    }

	  return $this->_redirect->setCode(301)
            			    	   ->setExit(true)
	                   		   ->gotoSimpleAndExit($action,
	                                         	   $controller,
	                                         	   $module,
	                                         	   $params);
  }
  
  protected function _url($args, $routeName)
  {
    $router = $this->getFrontController()->getRouter();
    return $router->assemble($args, $routeName);
  }
  
  protected function _initLocale()
  {
    $locale = Zend_Registry::get('config')->locale;
    $localeSession = new Zend_Session_Namespace('locale');
    
    if ($this->checkUserSession())
    {
      $locale = $this->_user->getDefaultLocale();
    }
    elseif (isset($localeSession->current))
    {
      $locale = $localeSession->current;
    }
    
    $localeSession->current = $locale;
    Zend_Registry::set('Zend_Locale', new Zend_Locale($locale));
    $this->view->language = Zend_Registry::get('Zend_Locale')->getLanguage();
    $this->view->locale = $locale;
  }

  protected function _initViewParams()
  {
    $request = $this->getRequest();
    // current module
    $this->view->cModule     = $request->getModuleName();
    // current controller
    $this->view->cController = $request->getControllerName();
    // current action
    $this->view->cAction     = $request->getActionName();
  }
  
  protected function _setDefaultTitle($attachOrder = false)
  {
    $t = new Custom_Translate();
    $title = $t->translate('headTitle', null, 'general');
    $this->_setTitle($title, $attachOrder = false);
  }
  
  protected function _setTitle($title, $attachOrder = false)
  {
    $bootstrap = $this->getInvokeArg('bootstrap');
    $view = $bootstrap->getResource('view');
    
    if ($attachOrder !== false)
    {
      $view->headTitle()->setDefaultAttachOrder($attachOrder);
    }

    $view->headTitle($title);
  }
  
  protected function _setTranslateTitle(array $parameters = null, $translateName = 'pageTitle', $attachOrder = false)
  {
    $t = new Custom_Translate();
    $title = $t->translate($translateName, $parameters);
    $this->_setTitle($title, $attachOrder);
    return $title;
  }
  
  protected function _setKeywords($keywords)
  {
    $this->view->keywords = $keywords;
  }
  
  protected function _setTranslateKeywords(array $parameters = null, $translateName = 'headKeywords')
  {
    $t = new Custom_Translate();
    $keywords = $t->translate($translateName, $parameters);
    $this->_setKeywords($keywords);
    return $keywords;
  }
  
  protected function _setDescription($description)
  {
    $this->view->description = $description;
  }
  
  protected function _setTranslateDescription(array $parameters = null, $translateName = 'headDescription')
  {
    $t = new Custom_Translate();
    $description = $t->translate($translateName, $parameters);
    $this->_setDescription($description);
    return $description;
  }
  
  protected function _initMessageBox()
  {
    $this->_messageBox = Custom_MessageBox::getInstance();
    $this->view->messageBox = $this->_messageBox;
  }
  
  public function checkUserSession($redirect = false, $isJson = false)
  {
    if (Zend_Auth::getInstance()->hasIdentity())
    {
      return true;
    }
    else
    {
      if ($redirect)
      {
        if ($this->getRequest()->isXmlHttpRequest())
        {
          // TOTHINK
          if ($isJson)
          {
            echo Zend_Json::encode(array('status' => self::STATUS_USER_NOT_LOGGED));
            exit();
          }
          
          echo '<div>Aby korzystać z tej opcji musisz być zalogowany. <a href="'.$this->getFrontController()->getBaseUrl().'/login">Zaloguj się</a></div>';
          exit;
        }
        else
        {
          $this->redirect(array(), 'user_login');
        }
      }
      
      return false;
    }
  }
  
  private function _setLoggedUser()
  {
    if (Zend_Auth::getInstance()->hasIdentity())
    {
      $userMapper = new Application_Model_UserMapper();
      
      $this->_user = $userMapper->getByEmail(new Application_Model_User(array('email' => Zend_Auth::getInstance()->getIdentity())));
      
      if ($this->_user instanceof Application_Model_User)
      {
        $this->view->authUser = $this->_user;
      }
      else
      {
        Application_Model_User::clearIdentity();
      }
    }
  }
  
  private function _rememberMeAutologin()
	{
    if( isset($_COOKIE['RememberMe']) && !Zend_Auth::getInstance()->hasIdentity() )
    {
      if (preg_match('/^([0-9]+)\.(.+)$/', $_COOKIE['RememberMe'], $matches))
      {
        $adapter = $this->_getRememberMeAuthAdapter(array('id' => $matches[1], 'last_login_date' => $matches[2]));
        $auth    = Zend_Auth::getInstance();
        $result  = $auth->authenticate($adapter);

        if ($result->isValid())
        {
          $userRowObject = $adapter->getResultRowObject(null, array('password', 'salt', 'token'));
          $auth->getStorage()->write($userRowObject->email);
          
          setcookie('FrameProfile', base64_encode($userRowObject->email), 0, '/', Zend_Registry :: get('config')->cookie_domain);
        }
      }
    }
  }
  
  private function _getRememberMeAuthAdapter(array $params)
  {
    $dbAdapter   = Zend_Db_Table::getDefaultAdapter();
    $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        
    $authAdapter->setTableName('user')
                ->setIdentityColumn('id')
                ->setCredentialColumn('last_login_date')
                ->setCredentialTreatment('FROM_UNIXTIME(?)')
                ->setIdentity($params['id'])
                ->setCredential($params['last_login_date']);
        
    return $authAdapter;
  }
  
  /*private function _initGeneralBackUrl()
  {
    $r = $this->getRequest();
    
    $exceptions = array(
      'user_edit' => array(
        array(
          'module'      => 'user',
          'controller'  => 'my-profile',
          'action'      => 'edit'
        ),
        array(
          'module'      => 'user',
          'controller'  => 'my-profile',
          'action'      => 'edit-process'
        ),
        array(
          'module'      => 'user',
          'controller'  => 'my-profile',
          'action'      => 'avatar-change'
        ),   
        array(
          'module'      => 'user',
          'controller'  => 'my-profile',
          'action'      => 'avatar-change-process'
        ),
        array(
          'module'      => 'user',
          'controller'  => 'my-profile',
          'action'      => 'avatar-delete'
        )
      )
    );
    
    foreach ($exceptions as $name => $items)
    {
      $exists = false;
      
      foreach ($items as $e)
      {
        if ($r->getModuleName() == $e['module'] && $r->getControllerName() == $e['controller'] && $r->getActionName() == $e['action'])
        {
          $exists = true;
          break;
        }
      }
      
      if (!$exists)
      {
        $this->_setCurrentBackUrl($name);
      }
    }
  }*/
    
  protected function _setBackUrl($name, $backUrl)
  {
    $session = new Zend_Session_Namespace('backUrl');
    $session->$name = array(
      'url'   => $backUrl,
      'route' => array(
        'name'    => null,
        'params'  => null
      )
    );
  }
  
  protected function _setRefererBackUrl($name)
  {
    $session = new Zend_Session_Namespace('backUrl');
    $session->$name = array(
      'url'   => $session->$name = Utils_Url::getReferer(),
      'route' => array(
        'name'    => null,
        'params'  => null
      )
    );
  }
  
  protected function _setCurrentBackUrl($name)
  {
    $params = $this->getRequest()->getParams();
    unset($params['module'], $params['controller'], $params['action']);
    $session = new Zend_Session_Namespace('backUrl');
    $session->$name = array(
      'url'   => Utils_Url::getCurrent(),
      'route' => array(
        'name'    => Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName(),
        'params'  => $params
      )
    );
  }
  
  protected function _getBackUrl($name, $defaultUrl, $clear = false)
  {
    $session   = new Zend_Session_Namespace('backUrl');
    $returnUrl = $defaultUrl;
    
    if (isset($session->$name) && !empty($session->$name))
    {
      $data      = $session->$name;
      $returnUrl = $data['url'];
    }
    
    if ($clear)
    {
      unset($session->$name);
    }
    
    return $returnUrl;
  }
  
  protected function _getBackRoute($name, $clear = false)
  {
    $session = new Zend_Session_Namespace('backUrl');
    
    if (!isset($session->$name) || empty($session->$name))
    {
      return false;
    }
    
    $data = $session->$name;
    
    if ($clear)
    {
      unset($session->$name);
    }
    
    return $data['route'];
  }
  
  private function _initActiveMenu()
  {
    $r = $this->getRequest();
    $m = $r->getModuleName();
    $c = $r->getControllerName();
    $a = $r->getActionName();
    
    $actions = array(
      'administration' => array(
        'index' => array(
          'index'   => 'dashboard'
        ),
        'bug-tracker' => array(
          'add-jira'            => 'projects',
          'add-jira-process'    => 'projects',
          'add-mantis'          => 'projects',
          'add-mantis-process'  => 'projects',
          'edit-jira'           => 'projects',
          'edit-jira-process'   => 'projects',
          'edit-mantis'         => 'projects',
          'edit-mantis-process' => 'projects',
          'index'               => 'projects',
          'view'                => 'projects'
        ),
        'project' => array(
          'add'             => 'projects',
          'add-process'     => 'projects',
          'edit'            => 'projects',
          'edit-process'    => 'projects',
          'export'          => 'projects',
          'export-process'  => 'projects',
          'import'          => 'projects',
          'import-process'  => 'projects',
          'index'           => 'projects',
          'view'            => 'projects'
        ),
        'resolution' => array(
          'add'            => 'projects',
          'add-process'    => 'projects',
          'edit'           => 'projects',
          'edit-process'   => 'projects',
          'delete'         => 'projects',
          'delete-process' => 'projects',
          'index'          => 'projects',
          'view'           => 'projects'
        ),
        'role' => array(
          'add'           => 'roles',
          'add-process'   => 'roles',
          'edit'          => 'roles',
          'edit-process'  => 'roles',
          'index'         => 'roles'
        ),
        'user' => array(
          'add'           => 'users',
          'add-process'   => 'users',
          'edit'          => 'users',
          'edit-process'  => 'users',
          'index'         => 'users'
        )
      ),
      'dashboard' => array(
        'index' => array(
          'index'   => 'dashboard'
        )
      ),
      'message' => array(
        'index' => array(
          'add'               => 'messages',
          'add-process'       => 'messages',
          'index'             => 'messages',
          'response'          => 'messages',
          'response-process'  => 'messages'
        )
      ),
      'project' => array(
        'defect' => array(
          'add'                   => 'defects',
          'add-process'           => 'defects',
          'assign'                => 'defects',
          'assign-process'        => 'defects',
          'edit'                  => 'defects',
          'edit-process'          => 'defects',
          'close'                 => 'defects',
          'close-process'         => 'defects',
          'index'                 => 'defects',
          'reopen'                => 'defects',
          'reopen-process'        => 'defects',
          'resolve-test'          => 'defects',
          'resolve-test-process'  => 'defects',
          'view'                  => 'defects'
        ),
        'environment' => array(
          'add'           => 'environments',
          'add-process'   => 'environments',
          'edit'          => 'environments',
          'edit-process'  => 'environments',
          'index'         => 'environments',
          'view'          => 'environments'
        ),
        'index' => array(
          'view' => 'project'
        ),
        'release' => array(
          'add'           => 'releases',
          'add-process'   => 'releases',
          'edit'          => 'releases',
          'edit-process'  => 'releases',
          'index'         => 'releases',
          'view'          => 'releases',
          'report'        => 'releases'
        ),
        'tag' => array(
          'add'           => 'tags',
          'add-process'   => 'tags',
          'edit'          => 'tags',
          'edit-process'  => 'tags',
          'index'         => 'tags',
          'view'          => 'tags'
        ),
        'task' => array(
          'add'                   => 'tasks',
          'add-process'           => 'tasks',
          'assign'                => 'tasks',
          'assign-process'        => 'tasks',
          'change-test'           => 'tasks',
          'change-test-process'   => 'tasks',
          'edit'                  => 'tasks',
          'edit-process'          => 'tasks',
          'close'                 => 'tasks',
          'close-process'         => 'tasks',
          'index'                 => 'tasks',
          'reopen'                => 'tasks',
          'reopen-process'        => 'tasks',
          'resolve-test'          => 'tasks',
          'resolve-test-process'  => 'tasks',
          'view'                  => 'tasks',
          'view-automatic-test'   => 'tasks',
          'view-checklist'        => 'tasks',
          'view-exploratory-test' => 'tasks',
          'view-other-test'       => 'tasks',
          'view-test-case'        => 'tasks'
        ),
        'task-test' => array(
          'view-automatic-test'   => 'tasks',
          'view-checklist'        => 'tasks',
          'view-exploratory-test' => 'tasks',
          'view-other-test'       => 'tasks',
          'view-test-case'        => 'tasks'
        ),
        'test' => array(
          'add-checklist'                     => 'tests',
          'add-checklist-process'             => 'tests',
          'add-exploratory-test'              => 'tests',
          'add-exploratory-test-process'      => 'tests',
          'add-other-test'                    => 'tests',
          'add-other-test-process'            => 'tests',
          'add-test-case'                     => 'tests',
          'add-test-case-process'             => 'tests',
          'edit-checklist'                    => 'tests',
          'edit-checklist-process'            => 'tests',
          'edit-exploratory-test'             => 'tests',
          'edit-exploratory-test-process'     => 'tests',
          'edit-other-test'                   => 'tests',
          'edit-other-test-process'           => 'tests',
          'edit-test-case'                    => 'tests',
          'edit-test-case-process'            => 'tests',
          'forward-to-execute'                => 'tests',//NIEUŻYWANE
          'forward-to-execute-process'        => 'tests',//NIEUŻYWANE
          'group-forward-to-execute'          => 'tests',//NIEUŻYWANE
          'group-forward-to-execute-process'  => 'tests',//NIEUŻYWANE
          'index'                             => 'tests',
          'view-checklist'                    => 'tests',
          'view-exploratory-test'             => 'tests',
          'view-other-test'                   => 'tests',
          'view-test-case'                    => 'tests'
        ),
        'version' => array(
          'add'           => 'versions',
          'add-process'   => 'versions',
          'edit'          => 'versions',
          'edit-process'  => 'versions',
          'index'         => 'versions',
          'view'          => 'versions'
        )
      )
    );
    
    $activeMenu = null;
    
    if (array_key_exists($m, $actions) && array_key_exists($c, $actions[$m]) && array_key_exists($a, $actions[$m][$c]))
    {
      $activeMenu = $actions[$m][$c][$a];
    }

    $this->view->activeMenu = $activeMenu;
  }
  
  private function _checkResetPassword()
  {
    if ($this->_user->getResetPassword())
    {
      $request = $this->getRequest();
      $m = $request->getModuleName();
      $c = $request->getControllerName();
      $a = $request->getActionName();

      if (!(
            ($m == 'user' && (($c == 'login' && $a == 'logout') || ($c == 'password' && ($a == 'reset' || $a == 'reset-process'))))
            || ($m == 'default' && $c == 'error')
      ))
      {
        $session = new Zend_Session_Namespace('userSession');
        $session->backUrl = $request->getScheme().'://'.$request->getHttpHost().$request->getRequestUri();
        $this->redirect(array(), 'user_reset_password');
      }
    }
  }
  
  protected function _initMultiSelectSession($name)
  {
    if (!array_key_exists('MultiSelect', $_SESSION) || !is_array($_SESSION['MultiSelect']))
    {
      $_SESSION['MultiSelect'] = array();
    }
    
    if (!array_key_exists($name, $_SESSION['MultiSelect']) || !is_array($_SESSION['MultiSelect'][$name]))
    {
      $_SESSION['MultiSelect'][$name] = array();
    }
  }
  
  protected function _removeIdFromMultiSelectIds($name, $id)
  {
    $this->_initMultiSelectSession($name);
    
    if (array_key_exists($id, $_SESSION['MultiSelect'][$name]))
    {
      unset($_SESSION['MultiSelect'][$name][$id]);
    }
  }
  
  protected function _getMultiSelectIds($name, $clear = true)
  {
    $this->_initMultiSelectSession($name);
    $ids = array();
    
    foreach ($_SESSION['MultiSelect'][$name] as $id => $checked)
    {
      if ($checked)
      {
        $ids[] = $id;
      }
    }
    
    if ($clear)
    {
      $_SESSION['MultiSelect'][$name] = array();
    }
    
    return $ids;
  }
  
  protected function _clearMultiSelectIds($name = null)
  {
    $this->_initMultiSelectSession($name);
    $_SESSION['MultiSelect'][$name] = array();
  }
}