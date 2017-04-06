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
class Project_Model_DefectJiraMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_DefectJiraDbTable';
  
  public function getByTask(Application_Model_Task $task, Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $rows = $this->_getDbTable()->getByTask($task->getId(), $projectBugTracker->getBugTrackerId());
    
    if (empty($rows))
    {
      return array();
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_DefectJira($row);
    }
    
    return $list;
  }
  
  public function getIdByNoForAjax(Application_Model_DefectJira $defectJira)
  {
    $id = $this->_getDbTable()->getIdByNo($defectJira->getNo(), $defectJira->getBugTracker()->getId());
    
    if (empty($id))
    {
      return false;
    }

    return $defectJira->setId($id);
  }
  
  public function add(Application_Model_DefectJira $defectJira)
  {
    $data = array(
      'bug_tracker_id'  => $defectJira->getBugTracker()->getId(),
      'no'              => $defectJira->getNo(),
      'summary'         => $defectJira->getSummary()
    );
    
    $defectJira->setId($this->_getDbTable()->insert($data));
  }
  
  public function save(Application_Model_DefectJira $defectJira)
  {
    $data = array(
      'summary' => $defectJira->getSummary()
    );
    
    $where = array(
      'id = ?' => $defectJira->getId()
    );
   
    $this->_getDbTable()->update($data, $where);
  }
  
  public function getForViewAjax(Application_Model_DefectJira $defectJira, Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $row = $this->_getDbTable()->getForViewAjax($defectJira->getId(), $projectBugTracker->getBugTrackerId());
    
    if (empty($row))
    {
      return false;
    }

    return $row->toArray();
  }
}