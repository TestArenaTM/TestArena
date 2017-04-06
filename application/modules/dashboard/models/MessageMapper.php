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
class Dashboard_Model_MessageMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Dashboard_Model_MessageDbTable';
  
  public function getLimitLatest(Application_Model_User $user, $limit = 5)
  {
    $rows = $this->_getDbTable()->getLimitLatest($user->getId(), $limit);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_Message($row);
    }
    
    return $list;
  }
  
  public function getNumberOfUnread(Application_Model_User $user)
  {
    return $this->_getDbTable()->getNumberOfUnread($user->getId());
  }
}