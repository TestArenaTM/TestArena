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
abstract class Custom_Model_WebService_Abstract
{
  protected $_name;
  protected $_client;
  
  public function __construct()
  {
    $config = Zend_Registry::get('config')->webService->get($this->_name);
    $options = array(
      'location'  => $config->uri, 
      'uri'       => $config->uri, 
      'trace'     => 1);
    
    if (strlen($config->login) && strlen($config->password))
    {
      $options['login'] = $config->login;
      $options['password'] = $config->password;
    }

    $this->_client = new SoapClient(null, $options);
    $this->_client->__setSoapHeaders(new SoapHeader('Security', 'Auth', array(
      'UserName' => $config->security->userName,
      'Password' => $config->security->password
    )));
  }
}