<?php
class LanguageController extends Custom_Controller_Action_Application_Abstract
{
  public function indexAction()
  {
    $request = $this->getRequest();
    $localeSession = new Zend_Session_Namespace('locale');
    $locale = $request->getParam('locale', null);

    if ($locale !== null)
    {
      $localeSession->current = $locale;
    
      if ($this->checkUserSession())
      {
        $this->_user->setDefaultLocale($locale);
        $userMapper = new Application_Model_UserMapper();
        $userMapper->changeDefaultLocale($this->_user);
      } else {
        setcookie('currentLocale', $locale, time() + (3600 * 24 * 360), '/');
      }

      $locale = new Zend_Locale($locale);
      //$frontController = Zend_Controller_Front::getInstance();
      $cache = Zend_Registry::get('cache');
      $routeConfig = new Zend_Config_Xml(_APPLICATION_CONFIG_PATH.'/routing/en.xml', 'items');
      $cache->save($routeConfig, 'routeConfig', array(), 24 * 3600);
      //$frontController->getRouter()->addConfig($routeConfig, 'routes');
    }

    $this->redirect($_SERVER['HTTP_REFERER']);
  }
}