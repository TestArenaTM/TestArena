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
class Application_Model_FilterMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Application_Model_FilterDbTable';

  public function getForUser(Application_Model_User $user)
  {
    $rows = $this->_getDbTable()->getForUserOnly($user->getId());

    if (null === $rows)
    {
      return false;
    }

    foreach ($rows->toArray() as $row)
    {
      $filter = new Application_Model_Filter($row);
      $filter->setUserObject($user);
      $user->addFilter($filter);
    }
    
    return $user;
  }

  public function getForUserByProject(Application_Model_User $user, Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getForUserByProject($user->getId(), $project->getId());      

    if (null === $rows)
    {
      return false;
    }

    foreach ($rows->toArray() as $row)
    {
      $filter = new Application_Model_Filter($row);
      $filter->setUserObject($user);
      $filter->setProjectObject($project);
      $user->addFilter($filter);
    }
    
    return $user;
  }

  public function save(Application_Model_Filter $filter)
  {
    $db = $this->_getDbTable();
    $data = array(
      'group' => $filter->getGroupId(),
      'data'  => $filter->getData(true)
    );
    
    if ($filter->getId() === null)
    {
      $data['user_id'] = $filter->getUser()->getId();
      $data['project_id'] = $filter->getProject() !== null ? $filter->getProject()->getId() : null;
      $filter->setId($db->insert($data));
    }
    else
    {
      $where = array(
        'id = ?' => $filter->getId()
      );
    
      $db->update($data, $where);
    }
  }
}