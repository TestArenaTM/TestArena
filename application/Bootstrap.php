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
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
  protected function _initConfig()
  {
    Zend_Registry::set('config', new Zend_Config($this->getOptions()));
  }
  
  protected function _initHeadTitle()
  {
    $this->bootstrap('view');
    $view = $this->getResource('view');
    $view->headTitle()
      ->setDefaultAttachOrder('PREPEND')
      ->setSeparator(' - '); 
  }
  
  protected function _initTimezone()
  {
    date_default_timezone_set(Zend_Registry::get('config')->timezone);
  }
  
  protected function _initLocale()
  {
    setlocale(LC_ALL, Zend_Registry::get('config')->locale);
  }
  
  protected function _initAutoload()
  {
    $loader = Zend_Loader_Autoloader::getInstance();
    $loader->registerNamespace('Utils_');
    $loader->registerNamespace('Custom_');
  }
  
  protected function _initCache()
  {
    Utils_Dir::checkDirExistAndCreate(_CACHEDIR);
    Utils_Dir::checkDirExistAndCreate(_CACHESESSDIR);
    Utils_Dir::checkDirExistAndCreate(_LOGDIR);
    
    $frontendOptions = array(
      'lifetime' => 7200, // cache lifetime of 2 hours
      'automatic_serialization' => true
    );
     
    $backendOptions = array(
      'cache_dir' => _CACHEDIR
    );
     
    $cache = Zend_Cache::factory('Core',
                                 'File',
                                 $frontendOptions,
                                 $backendOptions);
    
    if (isset($_REQUEST['clearCache']) || APPLICATION_ENV != 'production')
    {
      $cache->clean();
    }
    
    Zend_Registry::set('cache', $cache);
  }
  
  protected function _initSession()
  {
    if( !empty(Zend_Registry::get('config')->session->name) )
    {
      Zend_Session::setOptions( 
        array(
          'name' => Zend_Registry::get('config')->session->name,
          'cookie_domain' => Zend_Registry::get('config')->cookie_domain,
          'gc_maxlifetime' => Zend_Registry::get('config')->session->gc_maxlifetime,
          'save_path' => _CACHESESSDIR
        ) 
      );
    }
    Zend_Session::start();
  }
  
  protected function _initRegisterLog()
  {
    $this->bootstrap('Log');

    if (!$this->hasPluginResource('Log')) {
        throw new Zend_Exception('Log not enabled in config.ini');
    }
    
    $logger = $this->getResource('Log');
    assert($logger != null);
    Zend_Registry::set('Zend_Log', $logger);
    
    $config = Zend_Registry::get('config');
    
    if (!empty($config->log->info->path))
    {
      $logger = new Zend_Log();
      $logger->addWriter(new Zend_Log_Writer_Stream($config->log->info->path));
      Zend_Registry::set('infoLog', $logger);
    }
  }

  protected function _initRoutes()
  {
    $config = Zend_Registry::get('config');
    $this->bootstrap('frontController');
    $frontController = $this->getResource('frontController');
    $frontController->setBaseUrl($config->baseUrl);
    
    $cache = Zend_Registry::get('cache');
    
    if (!$routeConfig = $cache->load('routeConfig'))
    {
      $locale = new Zend_Locale($config->locale);
      
      $routeConfig = new Zend_Config_Xml(_APPLICATION_CONFIG_PATH.'/routing/'.$locale->getLanguage().'.xml', 'items');
      $cache->save($routeConfig, 'routeConfig', array(), 24 * 3600);
    }

    $frontController->getRouter()->addConfig($routeConfig, 'routes');
  }
  
  protected function _initFrontModules()
  {
    $this->bootstrap('frontController');
    $frontController = $this->getResource('frontController');
    $frontController->addModuleDirectory(APPLICATION_PATH . '/modules');
  }
  
  protected function _initDatabases()
  {
	$db = $this->bootstrap('db')->getResource('db');
    
    if (Zend_Registry::get('config')->resources->db->params->firebugEnabled 
        && APPLICATION_ENV == 'localmichalbuczek')
    {
      $profiler = new Zend_Db_Profiler_Firebug('All Database Queries:');
      
      $profiler->setEnabled(true);
      
      $db->setProfiler($profiler);
    }
    
    Zend_Registry::set('db', $db);
  }
}