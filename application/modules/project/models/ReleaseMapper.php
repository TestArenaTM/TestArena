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

  public function getForView(Application_Model_Release $release)
  {
    $row = $this->_getDbTable()->getForView($release->getId(), $release->getProjectId());
    
    if (null === $row)
    {
      return false;
    }

    return $release->setDbProperties($row->toArray());
  }

  public function getForEdit(Application_Model_Release $release)
  {
    $row = $this->_getDbTable()->getForEdit($release->getId(), $release->getProjectId());
    
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
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array(
      'project_id'  => $release->getProjectId(),
      'name'        => $release->getName(),
      'start_date'  => $release->getStartDate(),
      'end_date'    => $release->getEndDate(),
      'description' => $release->getDescription(),
      'active'      => $release->isActive()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      if ($release->isActive())
      {
        $db->update(array('active' => 0), array('project_id = ?' => $release->getProjectId()));
      }
      
      $db->insert($data);
      return (bool) $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollBack();
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function save(Application_Model_Release $release)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array(
      'name'        => $release->getName(),
      'start_date'  => $release->getStartDate(),
      'end_date'    => $release->getEndDate(),
      'description' => $release->getDescription(),
      'active'      => $release->isActive()     
    );

    $where = array(
      'id = ?' => $release->getId()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      if ($release->isActive())
      {
        $db->update(array('active' => 0), array('project_id = ?' => $release->getProjectId()));
      }
      
      $db->update($data, $where);      
      return (bool) $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollBack();
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
      'description' => $release->getDescription(),
      'active'      => $release->isActive()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      //add release
      
      if ($release->isActive())
      {
        $db->update(array('active' => 0), array('project_id = ?' => $release->getProjectId()));
      }
      
      $release->setExtraData('oldReleaseId', $release->getId());
      $release->setId($db->insert($releaseData));
      
      //get all of old release tasks
      $extraDataTasks = $release->getExtraData('tasks');
      
      if (count($extraDataTasks) > 0)
      {
        $taskMapper = new Project_Model_TaskMapper();
        $taskSql    = $taskMapper->getAllByReleaseByIds($extraDataTasks, $release, true);
        
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
            
            $newVersions = array_filter(explode(',', $release->getExtraData('versions', null)));
            if (count($newVersions) > 0)
            {
              $task->setExtraData('versions', $this->_prepareTaskVersions4Clone($task, $newVersions));
            }
            else
            {
              $currentVersions = array_filter(explode(',', $row['versions']));
              if (count($currentVersions) > 0)
              {
                $task->setExtraData('versions', $this->_prepareTaskVersions4Clone($task, $currentVersions));
              }
            }

            $newEnvironments = array_filter(explode(',', $release->getExtraData('environments', null)));
            if (count($newEnvironments) > 0)
            {
              $task->setExtraData('environments', $this->_prepareTaskEnvironments4Clone($task, $newEnvironments));
            }
            else
            {
              $currentEnvironments = array_filter(explode(',', $row['environments']));
              if (count($currentEnvironments) > 0)
              {
                $task->setExtraData('environments', $this->_prepareTaskEnvironments4Clone($task, $currentEnvironments));
              }
            }
            
            $tags = array_filter(explode(',', $row['tags']));
            if (count($tags) > 0)
            {
              $task->setExtraData('taskTags', $this->_prepareTaskTags4Clone($task, $tags));
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
      }
      
      return (bool) $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollBack();
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function activate(Application_Model_Release $release)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();

    try
    {
      $adapter->beginTransaction();
      
      $db->update(array(
        'active' => 0
      ), array(
        'project_id = ?' => $release->getProjectId(),
        'id != ?'        => $release->getId()
      ));
      
      $db->update(array(
        'active' => 1
      ), array(
        'id = ?' => $release->getId()
      ));
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollBack();
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function deactivate(Application_Model_Release $release)
  {
    try
    {
      $data = array(
        'active' => 0
      );
      
      $where = array(
        'id = ?' => $release->getId()
      );
      
      $this->_getDbTable()->update($data, $where) > 0;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return true;
  }
  
  public function delete(Application_Model_Release $release)
  {
    try
    {
      $this->_getDbTable()->delete(array('id = ?' => $release->getId()));
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
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
  
  public function getActive(Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getActive($project->getId());
    
    if (null === $row)
    {
      return null;
    }

    return new Application_Model_Release($row->toArray());
  }
  
  private function _prepareTaskTest4Clone(Application_Model_Task $task, array $row)
  {
    $taskTest = new Application_Model_TaskTest();
    $taskTest->setTask('id', $task->getId());
    $taskTest->setTest('id', $row['test$id']);
    $taskTest->setResolution('id', (!empty($row['resolutiuon$id']) ? $row['resolutiuon$id']: null));
    
    $checklistItems = array_filter(explode(',', $row['test$checklist_items']));
    if (count($checklistItems) > 0)
    {
      $this->_prepareTaskTestChecklistItems4Clone($taskTest, $checklistItems);
    }
    
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
  
  private function _prepareTaskTags4Clone(Application_Model_Task $task, array $tags)
  {
    $taskTags= array();
    
    foreach ($tags as $tagId)
    {
      $taskTag = new Application_Model_TaskTag();
      $taskTag->setTaskId($task->getId());
      $taskTag->setTagId($tagId);
      
      $taskTags[] = $taskTag;
    }
            
    return $taskTags;
  }
  
  private function _prepareTaskTestChecklistItems4Clone(Application_Model_TaskTest $taskTest, array $checklistItems)
  {
    if (count($checklistItems) > 0)
    {
      foreach ($checklistItems as $checklistItemId)
      {
        $taskChecklistItem = new Application_Model_TaskChecklistItem();
        $taskChecklistItem->setTaskTest('test$id', $taskTest->getTest()->getId());
        $taskChecklistItem->setChecklistItem('id', $checklistItemId);
        
        $taskTest->addChecklistItem($taskChecklistItem);
      }
    }
    
    return $this;
  }
  
  private function _prepareTask4Clone(Application_Model_Release $release, array $row)
  {
    $task = new Application_Model_Task();
    
    $task->setProjectObject($release->getProject());
    $task->setRelease('id', $release->getId());
    $task->setAssigneeObject($release->getExtraData('authUser'));
    $task->setAssignerObject($release->getExtraData('authUser'));
    $task->setCreateDate(date("Y-m-d H:i:s"));
    $task->setModifyDate(date("Y-m-d H:i:s"));
    $task->setDueDate($release->getExtraData('dueDate'));
    $task->setPriority($row['priority']);
    $task->setTitle($row['title']);
    $task->setDescription($row['title']);
    $task->setResolution('id', $row['resolution$id']);
    $task->setAuthor('id', $row['author$id']);

    $task->setExtraData('taskTests', array());
    $task->setExtraData('attachments', array());
    $task->setExtraData('versions', array());
    $task->setExtraData('environments', array());
    $task->setExtraData('taskTags', array());
    
    return $task;
  }
  
  public function createReport(Application_Model_Release $release)
  {
    /**** Obiekt File ****/
    $file = new Application_Model_File();
    $file->setDates(1);
    $file->setName($release->getName().' - '.$release->getExtraData('fileName').'_'.date('Ymd_His', strtotime($file->getCreateDate())), true);
    $file->setSubpath();
    $file->setDescription($release->getExtraData('fileDescription'));

    switch ($release->getExtraData('type'))
    {
      default:
      case Application_Model_Release::PDF_REPORT:
        $file->setExtension('pdf');
        $this->_createPdfReport($file, $release);
        break;
      
      case Application_Model_Release::CSV_REPORT:
        $file->setExtension('csv');
        $this->_createCsvReport($file, $release);
        break;
      
    }

    /**** Dodawanie pliku ****/
    $fileMapper = new Application_Model_FileMapper();
    
    if ($fileMapper->add($file))
    {
      $release->setExtraData('fileId', $file->getId());
      return true;
    }

    return false;
  }
  
  private function _createPdfReport(Application_Model_File $file, Application_Model_Release $release)
  {
    $taskMapper = new Project_Model_TaskMapper();
    $tasks = $taskMapper->getForPdfReportByRelease($release);
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $taskTestMapper->fillTasks($tasks);
    
    $html = new Zend_View();
    $html->addHelperPath(Zend_Registry::get('config')->resources->view->helperPath->Application_View_Helper);
    $html->setScriptPath(APPLICATION_PATH.'/views/pdfs/');
    $html->assign('name', 'release-report');
    $html->assign('release', $release);
    $html->assign('tasks', $tasks);
    $options['mode'] = Zend_Registry::get('config')->locale;
    
    $pdf = Custom_Pdf::create($options, _FRONT_PUBLIC_DIR.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'release-report.css'); 
    $pdf->SetDisplayMode('fullpage');
    $pdf->list_indent_first_level = 0;
    $pdf->WriteHTML($html->render('release-report.phtml'), 2);
    $pdf->setTitle($release->getName());
    $pdf->Output($file->getFullPath(true), 'F');
  }
  
  private function _createCsvReport(Application_Model_File $file, Application_Model_Release $release)
  {
    $name = 'csv_release-report';
    $taskMapper = new Project_Model_TaskMapper();
    $tasks = $taskMapper->getForCsvReportByRelease($release);
    $taskTestMapper = new Project_Model_TaskTestMapper();
    $taskTestMapper->fillTasks($tasks);

    $t = new Custom_Translate();
    $csvFile = new Utils_File_Writer_Csv($file->getFullPath(true));

    $csvFile->write(
      $t->translate('Zadanie', null, $name), 
      $t->translate('Test', null, $name),
      $t->translate('Rozwiązanie', null, $name)
    );
    
    foreach ($tasks as $task)
    {
      if (count($task->getTaskTests()) > 0)
      {
        $firstTest = true;

        foreach ($task->getTaskTests() as $taskTest)
        {
          $data = array();

          if ($firstTest)
          {
            $data[] = /*$task->getObjectNumber().' '.*/$task->getTitle();
            $firstTest = false;
          }
          else
          {
            $data[] = '';
          }

          $data[] = /*$taskTest->getTest()->getObjectNumber().' '.*/$taskTest->getTest()->getName();

          if ($taskTest->getResolution()->getId() > 0)
          {
            $data[] = $taskTest->getResolution()->getName();
          }
          else
          {
            $data[] = $t->translate('Nierozwiązany', null, $name);
          }

          $csvFile->writeRow($data);
        }
      }
      else          
      {
        $csvFile->write($task->getObjectNumber().' '.$task->getTitle());
      }
    }
    
    $csvFile->close();
  }

  public function getBasicById(Application_Model_Release $release)
  {
    $row = $this->_getDbTable()->getBasicById($release->getId());
    
    if (null === $row)
    {
      return false;
    }

    return $release->setDbProperties($row->toArray());
  }
}