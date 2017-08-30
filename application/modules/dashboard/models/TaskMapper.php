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
class Dashboard_Model_TaskMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Dashboard_Model_TaskDbTable';
  
  public function getLimitLastNotClosedAssignedToMe(Application_Model_User $user, 
    Application_Model_Project $project, Application_Model_Release $release, $limit = 5)
  {
    $rows = $this->_getDbTable()->getLimitLastNotClosedAssignedToMe($user->getId(), $project->getId(), $release->getId(), $limit);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_Task($row);
    }

    return $list;
  }
  
  public function countNotClosedAssignedToMe(Application_Model_User $user, 
    Application_Model_Project $project, Application_Model_Release $release)
  {
    return $this->_getDbTable()->countNotClosedAssignedToMe($user->getId(), $project->getId(), $release->getId());
  }
  
  public function countAll(Application_Model_Project $project, Application_Model_Release $release)
  {
    return $this->_getDbTable()->countAll($project->getId(), $release->getId());
  }
  
  public function countAssignedToMeGroupedByStatus(Application_Model_User $user, 
    Application_Model_Project $project, Application_Model_Release $release)
  {  
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();    
    $sql = $db->getSqlAssignedToMe($user->getId(), $project->getId(), $release->getId());
    $stmt = $adapter->query($sql);

    $result = array(
      'all'        => 0,
      'open'       => 0,
      'reopen'     => 0,
      'inProgress' => 0,
      'closed'     => 0
    );
    
    while ($row = $stmt->fetch())
    {      
      switch ($row['status']) {
        case Application_Model_TaskStatus::OPEN:
          $result['open']++;
          break;
        case Application_Model_TaskStatus::REOPEN:
          $result['reopen']++;
          break;
        case Application_Model_TaskStatus::IN_PROGRESS:
          $result['inProgress']++;
          break;
        case Application_Model_TaskStatus::CLOSED:
          $result['closed']++;
          break;
      }
      $result['all']++;
    }  
    
    return $result;
  }
  
  public function getLimitOverdueAssignedToMe(Application_Model_User $user, 
    Application_Model_Project $project, Application_Model_Release $release, $limit = 5)
  {
    $rows = $this->_getDbTable()->getLimitOverdueAssignedToMe($user->getId(), $project->getId(), $release->getId(), $limit);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_Task($row);
    }
    
    return $list;
  }
  
  public function countOverdueAssignedToMe(Application_Model_User $user, 
    Application_Model_Project $project, Application_Model_Release $release)
  {
    return $this->_getDbTable()->countOverdueAssignedToMe($user->getId(), $project->getId(), $release->getId());
  }
}