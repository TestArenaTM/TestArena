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
class Project_Model_UserDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'user';
  
  public function getByProjectAsOptions($projectId)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(u.firstname, " ", u.lastname)')
      ))
      ->join(array('ru' => 'role_user'), 'ru.user_id = u.id', array())
      ->join(array('r' => 'role'), 'r.id = ru.role_id', array())
      ->where('r.project_id = ?', $projectId)
      ->order('u.email')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
  
  public function getAlltAsOptions()
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'name' => 'email'
      ))
      ->order('u.email')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'name' => new Zend_Db_Expr('CONCAT(firstname, " ", lastname, " (", email, ")")')
      ))
      ->join(array('ru' => 'role_user'), 'ru.user_id = u.id', array())
      ->join(array('r' => 'role'), 'r.id = ru.role_id', array())
      ->setIntegrityCheck(false);
      
    $this->_setWhereCriteria($sql, $request);
    $this->_setOrderConditions($sql, $request);    

    return $this->fetchAll($sql);
  }
  
  public function getByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('u' => $this->_name), array(
        'id',
        'firstname',
        'lastname',
        'email'
      ))
      ->where('u.id IN (?)', $ids)
      ->order('u.id')
      ->limit(count($ids));
    
    return $this->fetchAll($sql);
  }
}