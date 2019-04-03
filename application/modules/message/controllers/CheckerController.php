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
class Message_CheckerController extends Zend_Controller_Action
{
  private $_user;

  public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
  {
    $this->_setLoggedUser();

    parent::__construct($request, $response, $invokeArgs);
  }

  public function newMessageInfoAjaxAction()
  {
    $message = new Message_Model_MessageMapper();
    $res = ($message->getUnreadMessageCountByUser($this->_user) > 0) ? 1 : 0;
    echo $res;
    exit;
  }

  private function _setLoggedUser()
  {
    if (Zend_Auth::getInstance()->hasIdentity())
    {
      $userMapper = new Application_Model_UserMapper();
      $this->_user = $userMapper->getByEmail(new Application_Model_User(array('email' => Zend_Auth::getInstance()->getIdentity())));
    }
  }

}