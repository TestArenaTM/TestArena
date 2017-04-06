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
class Application_Model_AuthorizationLog extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'user_id'   => 'userId',
    'user_type' => 'userType',
    'user_ip'   => 'userIp',
    'proxy_ip'  => 'proxyIp'
  );
  
  private $_id       = null;
  private $_userId   = null;
  private $_username = null;
  private $_type     = null;
  private $_userType = null;
  private $_time     = null;
  private $_userIp   = null;
  private $_proxyIp  = null;
  private $_browser  = null;

  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getUserId()
  {
    return $this->_userId;
  }

  public function getUser()
  {
    return $this->_user;
  }

  public function getUsername()
  {
    return $this->_username;
  }

  public function getType()
  {
    return $this->_type;
  }

  public function getTypeId()
  {
    return $this->_type !== null ? $this->_type->getId() : null;
  }

  public function getUserType()
  {
    return $this->_userType;
  }

  public function getUserTypeId()
  {
    return $this->_userType !== null ? $this->_userType->getId() : null;
  }

  public function getTime()
  {
    return $this->_time;
  }

  public function getUserIp()
  {
    return $this->_userIp;
  }

  public function getProxyIp()
  {
    return $this->_proxyIp;
  }

  public function getBrowser()
  {
    return $this->_browser;
  }

    // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }
  
  public function setUserId($userId)
  {
    $this->_userId = $userId;
    return $this;
  }

  public function setUsername($username)
  {
    $this->_username = $username;
    return $this;
  }
  
  public function setType($id)
  {
    $this->_type = new Application_Model_AuthorizationLogType($id);
    return $this;
  }
  
  public function setUserType($id)
  {
    $this->_userType = new Application_Model_AuthorizationLogUserType($id);
    return $this;
  }

  public function setTime($time)
  {
    $this->_time = $time;   
    return $this;
  }

  public function setUserIp($userIp)
  {
    $this->_userIp = $userIp;   
    return $this;
  }

  public function setProxyIp($proxyIp)
  {
    $this->_proxyIp = $proxyIp;   
    return $this;
  }

  public function setBrowser($browser)
  {
    $this->_browser = $browser;   
    return $this;
  }
  // </editor-fold>
  
  public function fillUserByUser(Application_Model_User $user)
  {
    $this->setUserType(Application_Model_AuthorizationLogUserType::USER);
    $this->setUsername($user->getEmail());
    $this->setUserId($user->getId()); 
    return $this;
  }
  
  public function fillBrowserData()
  {
    //$browser = new Utils_Browser();
    $this->setUserIp(Utils_Browser::getServerValue('REMOTE_ADDR'));
    $this->setProxyIp(Utils_Browser::getServerValue('HTTP_X_FORWARDED_FOR'));
    $this->setBrowser('Not known ');
    //$this->setBrowser($browser->getAgentName().' '.$browser->getAgentVersion());
  }
}