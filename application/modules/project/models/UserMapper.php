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
class Project_Model_UserMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_UserDbTable';
  
  public function getByProjectAsOptions(Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getByProjectAsOptions($project->getId());
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = $row['name'];
    }
    
    return $list;
  }
  
  public function getAllAsOptions()
  {
    $rows = $this->_getDbTable()->getAllAsOptions();
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = $row['name'];
    }
    
    return $list;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getAllAjax($request)->toArray();
  }
  
  public function getByIds(array $ids)
  {
    $rows = $this->_getDbTable()->getByIds($ids);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $user = new Application_Model_User($row);
      $list[$user->getId()] = $user;
    }
    
    return $list;
  }
}