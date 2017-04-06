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
class Application_Model_ProjectMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Application_Model_ProjectDbTable';

  public function getByUserId(Application_Model_User $user)
  {
    $rows = $this->_getDbTable()->getByUserId($user->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    return $rows->toArray();
  }
  
  public function getById(Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getById($project->getId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $project->setDbProperties($row->toArray());
  }
}