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
class Project_Model_ReleaseMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_ReleaseDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setCurrentPageNumber($request->getParam('page', 1));
    $resultCountPerPage = (int)$request->getParam('resultCountPerPage');
    $paginator->setItemCountPerPage($resultCountPerPage > 0 ? $resultCountPerPage : 10);
    
    $list = array();
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      $release = new Application_Model_Release($row);
      $list[] = $release;
    }
    
    return array($list, $paginator);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getAllAjax($request)->toArray();
  }
  
  public function getForForwardAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getForForwardAjax($request)->toArray();
  }
  
  public function getForPhaseAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getForPhaseAjax($request)->toArray();
  }

  public function getForView(Application_Model_Release $release)
  {
    $row = $this->_getDbTable()->getForView($release->getId());
    
    if (null === $row)
    {
      return false;
    }

    $release->setDbProperties($row->toArray());
    return $release->setExtraData('taskCount', $release->getExtraData('releaseTaskCount') + $release->getExtraData('phaseTaskCount'));
  }

  public function getForEdit(Application_Model_Release $release)
  {
    $row = $this->_getDbTable()->getForEdit($release->getId());
    
    if (null === $row)
    {
      return false;
    }

    $row = $row->toArray();
    $release->setDbProperties($row);
    return $release->map($row);
  }

  public function add(Application_Model_Release $release)
  {
    $data = array(
      'project_id'  => $release->getProjectId(),
      'name'        => $release->getName(),
      'start_date'  => $release->getStartDate(),
      'end_date'    => $release->getEndDate(),
      'description' => $release->getDescription()
    );
    
    return $this->_getDbTable()->insert($data) > 0;
  }
  
  public function save(Application_Model_Release $release)
  {
    $data = array(
      'name'        => $release->getName(),
      'start_date'  => $release->getStartDate(),
      'end_date'    => $release->getEndDate(),
      'description' => $release->getDescription()        
    );

    $where = array(
      'id = ?' => $release->getId()
    );
    
    try
    {
      $this->_getDbTable()->update($data, $where);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function cloneRelease(Application_Model_Release $release)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $releaseData = array(
      'project_id'  => $release->getProjectId(),
      'start_date'  => $release->getStartDate(),
      'end_date'    => $release->getEndDate(),
      'name'        => $release->getName(),
      'description' => $release->getDescription()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      //add release
      $release->setExtraData('oldReleaseId', $release->getId());
      $release->setId($db->insert($releaseData));
      
      //add phase
      $phaseName = $release->getExtraData('phaseName', null);
      $phase     = new Application_Model_Phase();
      
      if (isset($phaseName) && $phaseName != '')
      {
        $phaseMapper = new Project_Model_PhaseMapper();
        
        $phase->setRelease('id', $release->getId());
        $phase->setStartDate($release->getStartDate());
        $phase->setEndDate($release->getEndDate());
        $phase->setName($phaseName);
        $phase->setDescription($release->getDescription());
        
        $phase->setId($phaseMapper->add($phase));
      }
      
      $release->setPhase($phase);
      
      //get all of old release tasks
      $taskMapper = new Project_Model_TaskMapper();
      $taskSql    = $taskMapper->getAllByRelease($release, true);
      
      $stmt    = $adapter->query($taskSql);
      $tasks   = array();
      $taskCnt = 0;
      
      while ($row = $stmt->fetch())
      {
        if (false === array_key_exists($row['id'], $tasks))
        {
          // save tasks extra data for every 10 tasks
          if ($taskCnt > 9)
          {
            $taskMapper->saveClonedTasksExtraDataPatchByRelease($tasks);
            $tasks   = array();
            $taskCnt = 0;
          }
          
          //add task
          $task = $this->_prepareTask4Clone($release, $row);
          $task->setId($taskMapper->addClonedTask($task));
          
          if (null !== $row['test$id'] && Application_Model_TestStatus::ACTIVE == $row['test$status'])
          {
            $task->setExtraData('taskTests', array($row['test$id'] => $this->_prepareTaskTest4Clone($task, $row)));
          }

          if (null !== $row['attachment$file_id'])
          {
            $task->setExtraData('attachments', array($row['attachment$file_id'] => $this->_prepareTaskAttachment4Clone($task, $row)));
          }
          
          $versions = array_filter(explode(',', $release->getExtraData('versions', null)));
          if (count($versions) > 0)
          {
            $task->setExtraData('versions', $this->_prepareTaskVersions4Clone($task, $versions));
          }
          
          $environments = array_filter(explode(',', $release->getExtraData('environments', null)));
          if (count($environments) > 0)
          {
            $task->setExtraData('environments', $this->_prepareTaskEnvironments4Clone($task, $environments));
          }
          
          $tasks[$row['id']] = $task;
          $taskCnt++;
        }
        else
        {
          if (null !== $row['test$id'] 
              && Application_Model_TestStatus::ACTIVE == $row['test$status']
              && !array_key_exists($row['test$id'] , $tasks[$row['id']]->getExtraData('taskTests')))
          {
            $tasks[$row['id']]->setExtraData('taskTests', $tasks[$row['id']]->getExtraData('taskTests') + array($row['test$id'] => $this->_prepareTaskTest4Clone($tasks[$row['id']], $row)));
          }

          if (null !== $row['attachment$file_id'] 
              && !array_key_exists($row['attachment$file_id'] , $tasks[$row['id']]->getExtraData('attachments')))
          {
            $tasks[$row['id']]->setExtraData('attachments', $tasks[$row['id']]->getExtraData('attachments') + array($row['attachment$file_id'] => $this->_prepareTaskAttachment4Clone($tasks[$row['id']], $row)));
          }
        }
      }
      
      if (count($tasks) > 0)
      {
        $taskMapper->saveClonedTasksExtraDataPatchByRelease($tasks);
      }
      
      return (bool) $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollBack();
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return true;
  }
  
  public function delete(Application_Model_Release $release)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $phaseMapper = new Project_Model_PhaseMapper();

    try
    {
      $adapter->beginTransaction();
      $phaseMapper->deleteByRelease($release);
      $db->delete(array('id = ?' => $release->getId()));
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollBack();
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return true;
  }
  
  public function getByProjectAsOptions(Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getByProjectIdAsOptions($project->getId());
    
    $list = array();
    
    foreach($rows->toArray() as $row)
    {
      $list[$row['id']] = $row['name'];
    }
    
    return $list;
  }
  
  public function getForFilterAsOptions(Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getForFilterAsOptions($project->getId());
    
    $list = array();
    
    foreach($rows->toArray() as $row)
    {
      $list[$row['id']] = $row['name'];
    }
    
    return $list;
  }
  
  public function getForPhase(Application_Model_Release $release)
  {
    $row = $this->_getDbTable()->getForPhase($release->getId());

    if (null === $row)
    {
      return false;
    }

    $row = $row->toArray();
    $release->setDbProperties($row);
    return $release->map($row);
  }
  
  public function getForTask(Application_Model_Release $release, Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getForTask($release->getId(), $project->getId());
    
    if (null === $row)
    {
      return false;
    }

    $row = $row->toArray();
    $release->setDbProperties($row);
    return $release->map($row);
  }
  
  public function getByPhase(Application_Model_Phase $phase)
  {
    $row = $this->_getDbTable()->getByPhase($phase->getId());
    
    if (null === $row)
    {
      return false;
    }

    return new Application_Model_Release($row->toArray());
  }
  
  private function _prepareTaskTest4Clone(Application_Model_Task $task, array $row)
  {
    $taskTest = new Application_Model_TaskTest();
    $taskTest->setTask('id', $task->getId());
    $taskTest->setTest('id', $row['test$id']);
    $taskTest->setResolution('id', (!empty($row['resolutiuon$id']) ? $row['resolutiuon$id']: null));
    
    return $taskTest;
  }
  
  private function _prepareTaskAttachment4Clone(Application_Model_Task $task, array $row)
  {
    $attachment = new Application_Model_Attachment();
    $attachment->setFile('id', $row['attachment$file_id']);
    $attachment->setCreateDate($row['attachment$create_date']);
    $attachment->setSubjectId($task->getId());
    
    return $attachment;
  }
  
  private function _prepareTaskVersions4Clone(Application_Model_Task $task, array $versions)
  {
    $taskVersions = array();
    
    foreach ($versions as $versionId)
    {
      $taskVersion = new Application_Model_TaskVersion();
      $taskVersion->setTaskId($task->getId());
      $taskVersion->setVersionId($versionId);
      
      $taskVersions[] = $taskVersion;
    }
           
    return $taskVersions;
  }
  
  private function _prepareTaskEnvironments4Clone(Application_Model_Task $task, array $environments)
  {
    $taskEnvironments = array();
    
    foreach ($environments as $environmentId)
    {
      $taskEnvironment = new Application_Model_TaskEnvironment();
      $taskEnvironment->setTaskId($task->getId());
      $taskEnvironment->setEnvironmentId($environmentId);
      
      $taskEnvironments[] = $taskEnvironment;
    }
            
    return $taskEnvironments;
  }
  
  private function _prepareTask4Clone(Application_Model_Release $release, array $row)
  {
    $task = new Application_Model_Task();
    
    $task->setProjectObject($release->getProject());
    $task->setRelease('id', $release->getId());
    $task->setPhase('id', $release->getSinglePhase(0)->getId());
    $task->setAssigneeObject($release->getExtraData('authUser'));
    $task->setAssignerObject($release->getExtraData('authUser'));
    $task->setCreateDate($release->getStartDate());
    $task->setModifyDate($release->getStartDate());
    $task->setDueDate($release->getEndDate());
    $task->setPriority($row['priority']);
    $task->setTitle($row['title']);
    $task->setDescription($row['title']);
    $task->setResolution('id', $row['resolution$id']);
    $task->setAuthor('id', $row['author$id']);

    $task->setExtraData('taskTests', array());
    $task->setExtraData('attachments', array());
    $task->setExtraData('versions', array());
    $task->setExtraData('environments', array());
    
    return $task;
  }
}