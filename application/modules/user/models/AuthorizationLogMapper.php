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
class User_Model_AuthorizationLogMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'User_Model_AuthorizationLogDbTable';
  
  public function log(Application_Model_AuthorizationLog $authorizationLog)
  {
    $authorizationLog->fillBrowserData();
   
    try
    {
      $data = array(
        'user_id'   => $authorizationLog->getUserId(),
        'username'  => $authorizationLog->getUsername(),
        'type'      => $authorizationLog->getTypeId(),
        'user_type' => $authorizationLog->getUserTypeId(),
        'time'      => date('Y-m-d H:i:s'),
        'user_ip'   => $authorizationLog->getUserIp(),
        'proxy_ip'  => $authorizationLog->getProxyIp(),
        'browser'   => $authorizationLog->getBrowser()
      );

      return $this->_getDbTable()->insert($data);
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function logSuccessfulLogin(Application_Model_AuthorizationLog $authorizationLog)
  {
    $authorizationLog->setType(Application_Model_AuthorizationLogType::SUCCESSFUL_LOGIN);
    return $this->log($authorizationLog);
  }
  
  public function logFailedLogin(Application_Model_AuthorizationLog $authorizationLog)
  {
    $authorizationLog->setType(Application_Model_AuthorizationLogType::FAILED_LOGIN);
    return $this->log($authorizationLog);
  }
  
  public function logLogout(Application_Model_User $user)
  {
    $authorizationLog = new Application_Model_AuthorizationLog();
    $authorizationLog->fillUserByUser($user);
    $authorizationLog->setType(Application_Model_AuthorizationLogType::LOGOUT);
    return $this->log($authorizationLog);
  }
  
  /**
   * Sprawdza czy w ostatnim czasie $timeLimit określona liczba logować $rowLimit wszystkie logowania były negatywne.
   * @param type $rowLimit Ograniczenie liczby logowań
   * @param type $timeLimit Ograniczenie czasu logowań. Format DDD HH:MM:SS.mmmmmm
   * @return boolean
   */
  public function checkFailedLoginByLimit($rowLimit, $timeLimit)
  {
    $authorizationLog = new Application_Model_AuthorizationLog();
    $authorizationLog->fillBrowserData();
    $rows = $this->_getDbTable()->getLast(
      $authorizationLog->getUserIp(),
      $authorizationLog->getProxyIp(),
      $authorizationLog->getBrowser(),
      $rowLimit, $timeLimit
    );
    
    if ($rows === null)
    {
      return false;
    }
    
    $failedLoginCount = 0;
    
    foreach ($rows->toArray() as $row)
    {
      if ($row['type'] == Application_Model_AuthorizationLogType::FAILED_LOGIN)
      {
        $failedLoginCount++;
      }
    }
    
    return $failedLoginCount == $rowLimit;
  }
}