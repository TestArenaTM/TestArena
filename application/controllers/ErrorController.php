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
class ErrorController extends Custom_Controller_Action_Application_Abstract
{
  const VIEW_ERROR           = 'error';
  const VIEW_PAGE_NOT_FOUND  = 'page-not-found';
  const VIEW_ACCESS_DENIED   = 'access-denied';
  
  public function preDispatch()
  {
    if (!$this->checkUserSession())
    {
      $this->_helper->layout->setLayout('not-logged');
    }
    else
    {
      $this->_helper->layout->setLayout('clean');
    }
    
    $this->view->backUrl = $this->getRequest()->getServer('HTTP_REFERER');
    parent::preDispatch();
  }
  
  public function errorAction()
  {
    $errors = $this->_getParam('error_handler');
	
    if (!$errors || !$errors instanceof ArrayObject)
    {
      $this->render(self::VIEW_ERROR);
      return;
    }

    switch ($errors->type)
    {
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        $this->getResponse()->setHttpResponseCode(404);
        $priority = Zend_Log::NOTICE;
        $viewName = self::VIEW_PAGE_NOT_FOUND;
        break;
      
      default:
        if ($errors->exception instanceof Custom_AccessDeniedException)
        {
          $this->getResponse()->setHttpResponseCode(404);
          $priority = Zend_Log::NOTICE;
          $viewName = self::VIEW_ACCESS_DENIED;
        }        
        elseif ($errors->exception instanceof Custom_404Exception)
        {
          $this->getResponse()->setHttpResponseCode(404);
          $priority = Zend_Log::NOTICE;
          $viewName = self::VIEW_PAGE_NOT_FOUND;
        }
        else
        {
          $this->getResponse()->setHttpResponseCode(500);
          $priority = Zend_Log::CRIT;
          $viewName = self::VIEW_ERROR;
        }
        break;
    }

    if (($log = $this->getLog()) !== false)
    {
      $exception = $errors->exception;
      
      if (!($exception instanceof Custom_404Exception))
      {
        $log->log($exception->getMessage() . PHP_EOL . $exception->getTraceAsString(), $priority, $exception);
        $log->log('Request Parameters: ', $priority, $errors->request->getParams());
      }
    }
	
    if ($this->getInvokeArg('displayExceptions') == true)
    {
      $this->view->exception = $errors->exception;
    }
    
    $this->view->request = $errors->request;
    
    if ($viewName == self::VIEW_PAGE_NOT_FOUND && !$this->checkUserSession())
    {
      $viewName .= '-not-logged';
    }

    $this->render($viewName);
  }

  public function getLog()
  {
    $bootstrap = $this->getInvokeArg('bootstrap');
    if (!$bootstrap->hasResource('Log'))
    {
      return false;
    }
    $log = $bootstrap->getResource('Log');
    return $log;
  }
}