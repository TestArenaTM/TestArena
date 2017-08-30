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
class Application_Model_FilterDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'filter';

  public function getForUserOnly($userId)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'group',
        'data'
      ))      
      ->where('f.user_id = ?', $userId)
      ->where('f.project_id IS NULL');
    
    return $this->fetchAll($sql);
  }

  public function getForUserByProject($userId, $projectId)
  {
    $sqls[] = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'group',
        'data'
      ))      
      ->where('f.user_id = ?', $userId)
      ->where('f.project_id IS NULL')
      ->setIntegrityCheck();
    
    $sqls[] = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'group',
        'data'
      ))      
      ->where('f.user_id = ?', $userId)
      ->where('f.project_id = ?', $projectId)
      ->setIntegrityCheck();

    return $this->fetchAll($this->union($sqls));
  }
}