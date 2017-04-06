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
class User_Model_AuthorizationLogDbTable extends Zend_Db_Table_Abstract
{
  protected $_name = 'authorization_log';
  
  public function getLast($userIp, $proxyIp, $browser, $rowLimit, $timeLimit = '0:0:30')
  {
    $sql = $this->select()
      ->from(array('al' => $this->_name), array(
        'id',
        'type'        
      ))
      ->where('al.user_ip = ?', $userIp)
      ->where('al.browser = ?', $browser)
      ->where('al.time > ?', new Zend_Db_Expr('SUBTIME(NOW(), "'.$timeLimit.'")'))
      ->order('al.time DESC')
      ->limit($rowLimit);

    if (!empty($proxyIp))
    {
      $sql->where('al.proxy_ip = ?', $proxyIp);
    }
    
    return $this->fetchAll($sql);
  }
} 