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
class Administration_Model_ProjectBugTrackerMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_ProjectBugTrackerDbTable';
  
  public function getAllByProject(Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getAllByProject($project->getId());

    if ($rows === null)
    {
      return false;
    }
   
    $list = array(
      Application_Model_BugTrackerType::INTERNAL => null,
      Application_Model_BugTrackerType::JIRA => array(
        Application_Model_BugTrackerStatus::ACTIVE    => null,
        Application_Model_BugTrackerStatus::INACTIVE  => array()
      ),
      Application_Model_BugTrackerType::MANTIS => array(
        Application_Model_BugTrackerStatus::ACTIVE    => null,
        Application_Model_BugTrackerStatus::INACTIVE  => array()
      )
    );

    foreach ($rows->toArray() as $row)
    {
      $projectBugTracker = new Application_Model_ProjectBugTracker($row);
      
      if ($projectBugTracker->getBugTrackerTypeId() == Application_Model_BugTrackerType::INTERNAL)
      {
        $list[Application_Model_BugTrackerType::INTERNAL] = $projectBugTracker;
      }
      elseif ($projectBugTracker->getBugTrackerStatusId() == Application_Model_BugTrackerStatus::ACTIVE)
      {
        $list[$projectBugTracker->getBugTrackerTypeId()][Application_Model_BugTrackerStatus::ACTIVE] = $projectBugTracker;
      }
      else
      {
        $list[$projectBugTracker->getBugTrackerTypeId()][Application_Model_BugTrackerStatus::INACTIVE][] = $projectBugTracker;
      }      
    }

    return $list;
  }
  
  public function getForView(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $row = $this->_getDbTable()->getForView($projectBugTracker->getId());
    
    if ($row === null)
    {
      return false;
    }
    
    return $projectBugTracker->setDbProperties($row->toArray());
  }
  
  public function addInternal(Application_Model_Project $project)
  {
    $data = array(
      'project_id'         => $project->getId(),
      'name'               => 'INTERNAL',
      'bug_tracker_type'   => Application_Model_BugTrackerType::INTERNAL,
      'bug_tracker_status' => Application_Model_BugTrackerStatus::ACTIVE
    );

    return $this->_getDbTable()->insert($data);
  }
  
  public function addJira(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $bugTrackerJiraMapper = new Administration_Model_BugTrackerJiraMapper();
    
    try
    {
      $adapter->beginTransaction();
      $id = $bugTrackerJiraMapper->add($projectBugTracker->getBugTrackerJira());
      
      $projectBugTracker->setBugTrackerId($id);      
      $projectBugTracker->setBugTrackerType(Application_Model_BugTrackerType::JIRA);
      
      if ($projectBugTracker->getExtraData('activate'))
      {
        $db->update(
          array('bug_tracker_status' => Application_Model_BugTrackerStatus::INACTIVE), 
          array(
            'project_id = ?'          => $projectBugTracker->getProject()->getId(),
            'bug_tracker_status = ?'  => Application_Model_BugTrackerStatus::ACTIVE
          )
        );
        
        $projectBugTracker->setBugTrackerStatus(Application_Model_BugTrackerStatus::ACTIVE);
      }
      else
      {
        $projectBugTracker->setBugTrackerStatus(Application_Model_BugTrackerStatus::INACTIVE);
      }

      $data = array(
        'project_id'         => $projectBugTracker->getProject()->getId(),
        'bug_tracker_id'     => $projectBugTracker->getBugTrackerId(),
        'name'               => $projectBugTracker->getName(),
        'bug_tracker_type'   => $projectBugTracker->getBugTrackerTypeId(),
        'bug_tracker_status' => $projectBugTracker->getBugTrackerStatusId()
      );

      $db->insert($data);  
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function addMantis(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $bugTrackerMantisMapper = new Administration_Model_BugTrackerMantisMapper();
    
    try
    {
      $adapter->beginTransaction();
      $id = $bugTrackerMantisMapper->add($projectBugTracker->getBugTrackerMantis());
      
      $projectBugTracker->setBugTrackerId($id);      
      $projectBugTracker->setBugTrackerType(Application_Model_BugTrackerType::MANTIS);
      
      if ($projectBugTracker->getExtraData('activate'))
      {
        $db->update(
          array('bug_tracker_status' => Application_Model_BugTrackerStatus::INACTIVE), 
          array(
            'project_id = ?'          => $projectBugTracker->getProject()->getId(),
            'bug_tracker_status = ?'  => Application_Model_BugTrackerStatus::ACTIVE
          )
        );
        
        $projectBugTracker->setBugTrackerStatus(Application_Model_BugTrackerStatus::ACTIVE);
      }
      else
      {
        $projectBugTracker->setBugTrackerStatus(Application_Model_BugTrackerStatus::INACTIVE);
      }

      $data = array(
        'project_id'         => $projectBugTracker->getProject()->getId(),
        'bug_tracker_id'     => $projectBugTracker->getBugTrackerId(),
        'name'               => $projectBugTracker->getName(),
        'bug_tracker_type'   => $projectBugTracker->getBugTrackerTypeId(),
        'bug_tracker_status' => $projectBugTracker->getBugTrackerStatusId()
      );

      $db->insert($data);
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }

  public function getForEditJira(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $row = $this->_getDbTable()->getForEditJira($projectBugTracker->getId());
    
    if (null === $row)
    {
      return false;
    }

    return $projectBugTracker->map($row->toArray());
  }
  
  public function saveJira(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $bugTrackerJiraMapper = new Administration_Model_BugTrackerJiraMapper();

    try
    {
      $adapter->beginTransaction();
      $id = $bugTrackerJiraMapper->save($projectBugTracker->getBugTrackerJira());
      
      $projectBugTracker->setBugTrackerId($id);      
      $projectBugTracker->setBugTrackerType(Application_Model_BugTrackerType::JIRA);
      $data['name'] = $projectBugTracker->getName();
      
      if ($projectBugTracker->getExtraData('activate'))
      {
        $db->update(
          array('bug_tracker_status' => Application_Model_BugTrackerStatus::INACTIVE), 
          array(
            'project_id = ?'          => $projectBugTracker->getProject()->getId(),
            'bug_tracker_status = ?'  => Application_Model_BugTrackerStatus::ACTIVE
          )
        );
        
        $data['bug_tracker_status'] = Application_Model_BugTrackerStatus::ACTIVE;
      }
      
      $db->update($data, array('id = ?' => $projectBugTracker->getId()));
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }

  public function getForEditMantis(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $row = $this->_getDbTable()->getForEditMantis($projectBugTracker->getId());
    
    if (null === $row)
    {
      return false;
    }

    return $projectBugTracker->map($row->toArray());
  }
  
  public function saveMantis(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $bugTrackerMantisMapper = new Administration_Model_BugTrackerMantisMapper();

    try
    {
      $adapter->beginTransaction();
      $id = $bugTrackerMantisMapper->save($projectBugTracker->getBugTrackerMantis());
      
      $projectBugTracker->setBugTrackerId($id);      
      $projectBugTracker->setBugTrackerType(Application_Model_BugTrackerType::MANTIS);
      $data = array('name' => $projectBugTracker->getName());
      
      if ($projectBugTracker->getExtraData('activate'))
      {
        $db->update(
          array('bug_tracker_status' => Application_Model_BugTrackerStatus::INACTIVE), 
          array(
            'project_id = ?'          => $projectBugTracker->getProject()->getId(),
            'bug_tracker_status = ?'  => Application_Model_BugTrackerStatus::ACTIVE
          )
        );
        
        $data['bug_tracker_status']  = Application_Model_BugTrackerStatus::ACTIVE;
      }

      $db->update($data, array('id = ?' => $projectBugTracker->getId())); 
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function activate(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    if ($projectBugTracker->getId() === null || $projectBugTracker->getProject() === null || $projectBugTracker->getProject()->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'bug_tracker_status' => Application_Model_BugTrackerStatus::ACTIVE
    );
    
    $where = array(
      'id = ?'                  => $projectBugTracker->getId(),
      'bug_tracker_status = ?'  => Application_Model_BugTrackerStatus::INACTIVE
    );
    
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update(
        array('bug_tracker_status'  => Application_Model_BugTrackerStatus::INACTIVE), 
        array(
          'project_id = ?'          => $projectBugTracker->getProject()->getId(),
          'bug_tracker_status = ?'  => Application_Model_BugTrackerStatus::ACTIVE
        )
      );
      
      $db->update($data, $where);
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function delete(Application_Model_ProjectBugTracker $projectBugTracker)
  {
    $data = array(
      'bug_tracker_status' => Application_Model_BugTrackerStatus::DELETED
    );
    
    $where = array(
      'id = ?'                  => $projectBugTracker->getId(),
      'bug_tracker_type != ?'   => Application_Model_BugTrackerType::INTERNAL,
      'bug_tracker_status = ?'  => Application_Model_BugTrackerStatus::INACTIVE
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function getForExportByProject(Application_Model_Project $project)
  {
    try
    {
      $rows = $this->_getDbTable()->getForExportByProject($project->getId());
    
      if ($rows === null)
      {
        return false;
      }
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return $rows->toArray();
  }
  
  public function addForImport(Application_Model_Project $project, array $rows)
  {
    $db = $this->_getDbTable();
    
    foreach ($rows as $row)
    {
      $row['project_id'] = $project->getId();
      $db->insert($row);
    }
  }
}