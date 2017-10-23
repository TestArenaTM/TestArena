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
class Project_Model_TaskMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TaskDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request, Application_Model_Project $project)
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
      $task = new Application_Model_Task();
      $task->setProjectObject($project);
      $list[] = $task->setDbProperties($row);
    }

    return array($list, $paginator);
  }
  
  public function getAllIds(Zend_Controller_Request_Abstract $request)
  {
    $rows = $this->_getDbTable()->getAllIds($request);    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = $row['id'];
    }
    
    return $list;
  }
  
  public function getForEdit(Application_Model_Task $task)
  {
    $row = $this->_getDbTable()->getForEdit($task->getId(), $task->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    $task->setAssigner('id', $row->assignerId);
    $task->setAssignee('id', $row->assigneeId);
    $task->setAuthor('id', $row->authorId);

    return $task->map($row->toArray());
  }
  
  public function getAllByRelease(Application_Model_Release $release, $returnSql = false)
  {
    if ($returnSql)
    {
      return $this->_getDbTable()->getAllByRelease($release, $returnSql);
    }
    else
    {
      $rows = $this->_getDbTable()->getAllByRelease($release, $returnSql);
    }
    
    if (null === $rows)
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
  
  public function getAllByReleaseByIds(array $ids, Application_Model_Release $release, $returnSql = false)
  {
    if ($returnSql)
    {
      return $this->_getDbTable()->getAllByReleaseByIds($ids, $release, $returnSql);
    }
    else
    {
      $rows = $this->_getDbTable()->getAllByReleaseByIds($ids, $release, $returnSql);
    }
    
    if (null === $rows)
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
  
  public function getByIds(array $ids, $returnSql = false)
  {
    if (count($ids) === 0)
    {
      return array();
    }
    
    $rows = $this->_getDbTable()->getByIds($ids, $returnSql);
    
    if ($returnSql)
    {
      return $rows;
    }
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = new Application_Model_Task($row);
    }

    return $list;
  }
  
  public function getByIds4CheckAccess(array $ids)
  {
    if (count($ids) === 0)
    {
      return array();
    }
    
    $rows = $this->_getDbTable()->getByIds4CheckAccess($ids);
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = new Application_Model_Task($row);
    }

    return $list;
  }
  
  public function getForView(Application_Model_Task $task)
  {
    $row = $this->_getDbTable()->getForView($task->getId(), $task->getProjectId());
    
    if (null === $row)
    {
      return false;
    }

    return $task->setDbProperties($row->toArray());
  }
  
  public function add(Application_Model_Task $task)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $date = date('Y-m-d H:i:s');
    
    $data = array(
      'project_id'  => $task->getProject()->getId(),
      'assigner_id' => $task->getAssignerId(),
      'assignee_id' => $task->getAssigneeId(),
      'create_date' => $date,
      'modify_date' => $date,
      'due_date'    => $task->getDueDate(),
      'status'      => Application_Model_TaskStatus::OPEN,
      'priority'    => $task->getPriorityId(),
      'title'       => $task->getTitle(),
      'description' => $task->getDescription(),
      'author_id'   => $task->getAuthorId()
    );
    
    if ($task->getRelease()->getId() > 0)
    {
      $data['release_id'] = $task->getRelease()->getId();
    }

    try
    {
      $adapter->beginTransaction();
      $task->setId($db->insert($data));

      $taskEnvironmentMapper = new Project_Model_TaskEnvironmentMapper();
      $taskEnvironmentMapper->save($task);

      $taskVersionMapper = new Project_Model_TaskVersionMapper();
      $taskVersionMapper->save($task);

      $taskTagMapper = new Project_Model_TaskTagMapper();
      $taskTagMapper->save($task);

      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTask($task);

      if ($adapter->commit())
      {
        $task->setCreateDate($date);
        return true;
      }
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
    }
    
    return false;
  }
  
  public function save(Application_Model_Task $task)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();    
    
    $data = array(
      'assigner_id' => $task->getAssignerId(),
      'assignee_id' => $task->getAssigneeId(),
      'modify_date' => date('Y-m-d H:i:s'),
      'due_date'    => $task->getDueDate(),
      'priority'    => $task->getPriorityId(),
      'title'       => $task->getTitle(),
      'description' => $task->getDescription(),
      'release_id'  => null
    );

    if ($task->getRelease()->getId() > 0)
    {
      $data['release_id'] = $task->getRelease()->getId();
    }

    try
    {
      $adapter->beginTransaction();
      $db->update($data, array('id = ?' => $task->getId()));

      $taskEnvironment = new Application_Model_TaskEnvironment();
      $taskEnvironment->setTaskId($task->getId());
      $taskEnvironmentMapper = new Project_Model_TaskEnvironmentMapper();
      $taskEnvironmentMapper->save($task);
      
      $taskVersion = new Application_Model_TaskVersion();
      $taskVersion->setTaskId($task->getId());
      $taskVersionMapper = new Project_Model_TaskVersionMapper();
      $taskVersionMapper->save($task);
      
      $taskTag = new Application_Model_TaskTag();
      $taskTag->setTaskId($task->getId());
      $taskTagMapper = new Project_Model_TaskTagMapper();
      $taskTagMapper->save($task);
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTask($task);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function addGroup(Application_Model_Task $task)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $taskIds = $task->getExtraData('taskIds');
    $environmentIds = $task->getExtraData('environments');
    $versionIds = $task->getExtraData('versions');
    $tagIds = $task->getExtraData('tags');
    
    $data = array(
      'task_id'     => 0,
      'assigner_id' => $task->getAssignerId(),
      'assignee_id' => $task->getAssigneeId(),
      'status'      => Application_Model_TaskStatus::OPEN,
      'priority'    => $task->getPriorityId(),
      'create_date' => date('Y-m-d H:i:s'),
      'due_date'    => $task->getDueDate()
    );

    try
    {
      $adapter->beginTransaction();
      $taskIds = array();
      
      foreach ($taskIds as $id)
      {
        $data['task_id'] = $id;
        $task->setId($db->insert($data));
        $taskIds[] = $task->getId();

        $taskEnvironment = new Application_Model_TaskEnvironment();
        $taskEnvironment->setTaskId($task->getId());
        $taskEnvironmentMapper = new Project_Model_TaskEnvironmentMapper();

        foreach ($environmentIds as $environmentId)
        {
          $taskEnvironment->setEnvironmentId($environmentId);
          $taskEnvironmentMapper->add($taskEnvironment);
        }
      }
      
      $taskVersion = new Application_Model_TaskVersion();
      $taskVersion->setTaskId($task->getId());
      $taskVersionMapper = new Project_Model_TaskVersionMapper();
      
      foreach ($versionIds as $versionId)
      {
        $taskVersion->setVersionId($versionId);
        $taskVersionMapper->add($taskVersion);
      }
      
      $taskTag = new Application_Model_TaskTag();
      $taskTag->setTaskId($task->getId());
      $taskTagMapper = new Project_Model_TaskTagMapper();
      
      foreach ($tagIds as $tagId)
      {
        $taskTag->setTagId($tagId);
        $taskTagMapper->add($taskTag);
      }

      $commentContent = Utils_Text::unicodeTrim($task->getExtraData('comment'));
      
      if (!empty($commentContent))
      {
        $comment = new Application_Model_Comment();
        $comment->setContent($commentContent);
        $comment->setUserObject($task->getAssigner());
        $comment->setSubjectType(Application_Model_CommentSubjectType::TASK_RUN);
        $commentMapper = new Project_Model_CommentMapper();
        
        foreach ($taskIds as $taskId)
        {
          $comment->setSubjectId($taskId);
          
          if ($commentMapper->add($comment) === false)
          {
            throw new Exception('[AddGroupTask] Comment adding is failed');
          }
        }
      }
      
      $task->setExtraData('taskIds', $taskIds);
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function addClonedTask(Application_Model_Task $task)
  {
    $data = array(
      'project_id'  => $task->getProject()->getId(),
      'assigner_id' => $task->getAssignerId(),
      'assignee_id' => $task->getAssigneeId(),
      'create_date' => $task->getCreateDate(),
      'modify_date' => $task->getModifyDate(),
      'due_date'    => $task->getDueDate(),
      'status'      => Application_Model_TaskStatus::OPEN,
      'priority'    => $task->getPriorityId(),
      'title'       => $task->getTitle(),
      'description' => $task->getDescription(),
      'author_id'   => $task->getAuthorId()
    );
    
    if ($task->getRelease()->getId() > 0)
    {
      $data['release_id'] = $task->getRelease()->getId();
    }

    return $this->_getDbTable()->insert($data);
  }
  
  public function saveClonedTasksExtraDataPatchByRelease(array $tasks)
  {
    $taskVersions           = array();
    $taskEnvironments       = array();
    $taskTests              = array();
    $taskAttachments        = array();
    $taskTags               = array();
    
    foreach($tasks as $task)
    {
      $taskVersions           = array_merge($taskVersions, $task->getExtraData('versions'));
      $taskEnvironments       = array_merge($taskEnvironments, $task->getExtraData('environments'));
      $taskTests              = array_merge($taskTests, $task->getExtraData('taskTests'));
      $taskAttachments        = array_merge($taskAttachments, $task->getExtraData('attachments'));
      $taskTags               = array_merge($taskTags, $task->getExtraData('taskTags'));
    }
    
    if (count($taskEnvironments) > 0)
    {
      $taskEnvironmentMapper = new Project_Model_TaskEnvironmentMapper();
      $taskEnvironmentMapper->saveGroup($taskEnvironments);
    }

    if (count($taskVersions) > 0)
    {
      $taskVersionMapper = new Project_Model_TaskVersionMapper();
      $taskVersionMapper->saveGroup($taskVersions);
    }

    if (count($taskTests) > 0)
    {
      $taskTestMapper = new Project_Model_TaskTestMapper();
      $taskTestMapper->saveGroup($taskTests);

      $taskTestChecklistItems = array();

      foreach ($taskTests as $taskTest)
      {
        //tmp solution
        $currentTaskTestChecklistItems = $taskTest->getChecklistItems();

        if (count($currentTaskTestChecklistItems) > 0)
        {
          $taskTestId = $taskTestMapper->getIdByTaskTestData($taskTest);

          if ($taskTestId > 0)
          {
            foreach ($currentTaskTestChecklistItems as $currentTaskTestChecklistItem)
            {
              $currentTaskTestChecklistItem->setTaskTest('id', $taskTestId);
            }

            $taskTestChecklistItems = array_merge($taskTestChecklistItems, $currentTaskTestChecklistItems);
          }
        }
        //tmp solution end
      }

      if (count($taskTestChecklistItems) > 0)
      {
        $taskChecklistItemMapper = new Project_Model_TaskChecklistItemMapper();
        $taskChecklistItemMapper->saveGroup($taskTestChecklistItems);
      }
    }

    if (count($taskAttachments) > 0)
    {
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveGroupForTasks($taskAttachments);
    }

    if (count($taskTags) > 0)
    {
      $tagMapper = new Project_Model_TaskTagMapper();
      $tagMapper->saveGroup($taskTags);
    }

    return true;
  }
  
  public function delete(Application_Model_Task $task)
  {
    if ($task->getId() === null)
    {
      return false;
    }

    $db = $this->_getDbTable();    
    $adapter = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();
      
      $taskTestMapper = new Project_Model_TaskTestMapper();
      $taskTestIds = $taskTestMapper->getIdsByTask($task);
      
      if (count($taskTestIds) > 0)
      {
        $commentMapper = new Project_Model_CommentMapper();
        $commentMapper->deleteByTaskTestIds($taskTestIds);
      
        $historyMapper = new Project_Model_HistoryMapper();
        $historyMapper->deleteByTaskTestIds($taskTestIds);
      
        $taskChecklistItemMapper = new Project_Model_TaskChecklistItemMapper();
        $taskChecklistItemMapper->deleteByTaskTestIds($taskTestIds);
        
        $taskTestMapper->deleteByTask($task);
      }
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->deleteByTask($task);
      
      $commentMapper = new Project_Model_CommentMapper();
      $commentMapper->deleteByTask($task);
      
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->deleteByTask($task);
      
      $taskDefectMapper = new Project_Model_TaskDefectMapper();
      $taskDefectMapper->deleteByTask($task);
      
      $taskEnvironmentMapper = new Project_Model_TaskEnvironmentMapper();
      $taskEnvironmentMapper->deleteByTask($task);
      
      $taskTagMapper = new Project_Model_TaskTagMapper();
      $taskTagMapper->deleteByTask($task);
      
      $taskVersionMapper = new Project_Model_TaskVersionMapper();
      $taskVersionMapper->deleteByTask($task);
      
      $db->delete(array(
        'id = ?' => $task->getId()
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
  
  public function deleteByIds(array $taskIds)
  {
    if (count($taskIds) == 0)
    {
      return true;
    }

    $db = $this->_getDbTable();    
    $adapter = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();
      
      $taskTestMapper = new Project_Model_TaskTestMapper();
      $taskTestIds = $taskTestMapper->getIdsByTaskIds($taskIds);
      
      if (count($taskTestIds) > 0)
      {
        $commentMapper = new Project_Model_CommentMapper();
        $commentMapper->deleteByTaskTestIds($taskTestIds);
      
        $historyMapper = new Project_Model_HistoryMapper();
        $historyMapper->deleteByTaskTestIds($taskTestIds);
      
        $taskChecklistItemMapper = new Project_Model_TaskChecklistItemMapper();
        $taskChecklistItemMapper->deleteByTaskTestIds($taskTestIds);
        
        $taskTestMapper->deleteByIds($taskTestIds);
      }
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->deleteByTaskIds($taskIds);
      
      $commentMapper = new Project_Model_CommentMapper();
      $commentMapper->deleteByTaskIds($taskIds);
      
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->deleteByTaskIds($taskIds);
      
      $taskDefectMapper = new Project_Model_TaskDefectMapper();
      $taskDefectMapper->deleteByTaskIds($taskIds);
      
      $taskEnvironmentMapper = new Project_Model_TaskEnvironmentMapper();
      $taskEnvironmentMapper->deleteByTaskIds($taskIds);
      
      $taskTagMapper = new Project_Model_TaskTagMapper();
      $taskTagMapper->deleteByTaskIds($taskIds);
      
      $taskVersionMapper = new Project_Model_TaskVersionMapper();
      $taskVersionMapper->deleteByTaskIds($taskIds);
      
      $db->delete(array(
        'id IN(?)' => $taskIds
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
  
  public function start(Application_Model_Task $task)
  {
    if ($task->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_TaskStatus::IN_PROGRESS
    );
    
    $where = array(
      'status IN(?)' => array(
        Application_Model_TaskStatus::OPEN,
        Application_Model_TaskStatus::REOPEN
      ),
      'id = ?' => $task->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function assign(Application_Model_Task $task)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array(
      'assignee_id' => $task->getAssigneeId(),
      'status'      => $task->getStatusId(),
      'modify_date' => date('Y-m-d H:i:s')
    );

    try
    {
      $adapter->beginTransaction();
      $db->update($data, array('id = ?' => $task->getId()));
      
      $commentContent = Utils_Text::unicodeTrim($task->getExtraData('comment'));
      
      if (!empty($commentContent))
      {
        $comment = new Application_Model_Comment();
        $comment->setContent($commentContent);
        $comment->setUserObject($task->getAssigner());
        $comment->setSubjectId($task->getId());
        $comment->setSubjectType(Application_Model_CommentSubjectType::TASK);
        $commentMapper = new Project_Model_CommentMapper();

        if ($commentMapper->add($comment) === false)
        {
          throw new Exception('[AssignTask] Comment adding is failed');
        }
      }
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function close(Application_Model_Task $task)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array(
      'status'        => Application_Model_TaskStatus::CLOSED,
      'resolution_id' => $task->getResolutionId(),
      'modify_date'   => date('Y-m-d H:i:s')
    );

    try
    {
      $adapter->beginTransaction();
      $db->update($data, array('id = ?' => $task->getId()));

      $taskEnvironment = new Application_Model_TaskEnvironment();
      $taskEnvironment->setTaskId($task->getId());
      $taskEnvironmentMapper = new Project_Model_TaskEnvironmentMapper();
      $taskEnvironmentMapper->save($task);
      
      $taskVersion = new Application_Model_TaskVersion();
      $taskVersion->setTaskId($task->getId());
      $taskVersionMapper = new Project_Model_TaskVersionMapper();
      $taskVersionMapper->save($task);
      
      $commentContent = Utils_Text::unicodeTrim($task->getExtraData('comment'));
      
      if (!empty($commentContent))
      {
        $comment = new Application_Model_Comment();
        $comment->setContent($commentContent);
        $comment->setUserObject($task->getAssignee());
        $comment->setSubjectId($task->getId());
        $comment->setSubjectType(Application_Model_CommentSubjectType::TASK);
        $commentMapper = new Project_Model_CommentMapper();

        if ($commentMapper->add($comment) === false)
        {
          throw new Exception('[EndTask] Comment adding is failed');
        }
      }

      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function getForPdfReportByRelease(Application_Model_Release $release)
  {
    $rows = $this->_getDbTable()->getForPdfReportByRelease($release);  
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $task = new Application_Model_Task($row);
      $list[$task->getId()] = $task;
    }
    
    return $list;
  }
  
  public function getForCsvReportByRelease(Application_Model_Release $release)
  {
    $rows = $this->_getDbTable()->getForCsvReportByRelease($release);  
    $tasks = array();
    
    foreach ($rows->toArray() as $row)
    {
      $task = new Application_Model_Task($row);
      $tasks[$task->getId()] = $task;
    }
    
    return $tasks;
  }
  
  public function getTasks4ReleaseCloneByRelease(Application_Model_Release $release, $returnSql = false)
  {
    if ($returnSql)
    {
      return $this->_getDbTable()->getTasks4ReleaseCloneByRelease($release, $returnSql);
    }
    else
    {
      $rows = $this->_getDbTable()->getTasks4ReleaseCloneByRelease($release, $returnSql);
    }
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $task   = new Application_Model_Task($row);
      $task->setProjectObject($release->getProject());
      $list[] = $task;
    }

    return $list;
  }}