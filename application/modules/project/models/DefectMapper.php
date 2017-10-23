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
class Project_Model_DefectMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_DefectDbTable';
  
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
      $defect = new Application_Model_Defect();
      $defect->setProjectObject($project);
      $list[] = $defect->setDbProperties($row);
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
  
  public function add(Application_Model_Defect $defect)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();    
    $date = date('Y-m-d H:i:s');
    
    $data = array(
      'project_id'  => $defect->getProject()->getId(),
      'assigner_id' => $defect->getAssignerId(),
      'assignee_id' => $defect->getAssigneeId(),
      'create_date' => $date,
      'modify_date' => $date,
      'status'      => Application_Model_DefectStatus::OPEN,
      'priority'    => $defect->getPriorityId(),
      'title'       => $defect->getTitle(),
      'description' => $defect->getDescription(),
      'author_id'   => $defect->getAuthorId()
    );
    
    if ($defect->getRelease()->getId() > 0)
    {
      $data['release_id'] = $defect->getRelease()->getId();
    }

    try
    {
      $adapter->beginTransaction();
      $defect->setId($db->insert($data));

      $defectEnvironmentMapper = new Project_Model_DefectEnvironmentMapper();
      $defectEnvironmentMapper->save($defect);

      $defectVersionMapper = new Project_Model_DefectVersionMapper();
      $defectVersionMapper->save($defect);

      $defectTagMapper = new Project_Model_DefectTagMapper();
      $defectTagMapper->save($defect);

      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveDefect($defect);

      if ($adapter->commit())
      {
        $defect->setCreateDate($date);
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
  
  public function save(Application_Model_Defect $defect)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();    
    
    $data = array(
      'assigner_id' => $defect->getAssignerId(),
      'assignee_id' => $defect->getAssigneeId(),
      'modify_date' => date('Y-m-d H:i:s'),
      'priority'    => $defect->getPriorityId(),
      'title'       => $defect->getTitle(),
      'description' => $defect->getDescription(),
      'release_id'  => null
    );

    if ($defect->getRelease()->getId() > 0)
    {
      $data['release_id'] = $defect->getRelease()->getId();
    }

    try
    {
      $adapter->beginTransaction();
      $db->update($data, array('id = ?' => $defect->getId()));

      $defectEnvironmentMapper = new Project_Model_DefectEnvironmentMapper();
      $defectEnvironmentMapper->save($defect);
      
      $defectVersionMapper = new Project_Model_DefectVersionMapper();
      $defectVersionMapper->save($defect);

      $defectTagMapper = new Project_Model_DefectTagMapper();
      $defectTagMapper->save($defect);
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveDefect($defect);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }
  
  public function getForView(Application_Model_Defect $defect)
  {
    $row = $this->_getDbTable()->getForView($defect->getId(), $defect->getProjectId());
    
    if (null === $row)
    {
      return false;
    }

    return $defect->setDbProperties($row->toArray());
  }
  
  public function getForEdit(Application_Model_Defect $defect)
  {
    $row = $this->_getDbTable()->getForEdit($defect->getId(), $defect->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    $defect->setAssignee('id', $row->assigneeId);
    $defect->setAssigner('id', $row->assignerId);
    $defect->setAuthor('id', $row->authorId);
    return $defect->map($row->toArray());
  }
  
  public function changeStatus(Application_Model_Defect $defect, array $oldStatus)
  {
    $data = array(
      'status' => $defect->getStatusId()
    );
    
    $where = array(
      'status IN(?)' => $oldStatus,
      'id = ?' => $defect->getId()
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
  
  public function delete(Application_Model_Defect $defect)
  {
    if ($defect->getId() === null)
    {
      return false;
    }

    $db = $this->_getDbTable();    
    $adapter = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->deleteByDefect($defect);
      
      $commentMapper = new Project_Model_CommentMapper();
      $commentMapper->deleteByDefect($defect);
      
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->deleteByDefect($defect);
      
      $defectEnvironmentMapper = new Project_Model_DefectEnvironmentMapper();
      $defectEnvironmentMapper->deleteByDefect($defect);
      
      $defectTagMapper = new Project_Model_DefectTagMapper();
      $defectTagMapper->deleteByDefect($defect);
      
      $defectVersionMapper = new Project_Model_DefectVersionMapper();
      $defectVersionMapper->deleteByDefect($defect);
      
      $db->delete(array(
        'id = ?' => $defect->getId()
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
  
  public function deleteByIds(array $defectIds)
  {
    if (count($defectIds) == 0)
    {
      return true;
    }

    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->deleteByDefectIds($defectIds);
      
      $commentMapper = new Project_Model_CommentMapper();
      $commentMapper->deleteByDefectIds($defectIds);
      
      $historyMapper = new Project_Model_HistoryMapper();
      $historyMapper->deleteByDefectIds($defectIds);
      
      $defectEnvironmentMapper = new Project_Model_DefectEnvironmentMapper();
      $defectEnvironmentMapper->deleteByDefectIds($defectIds);
      
      $defectTagMapper = new Project_Model_DefectTagMapper();
      $defectTagMapper->deleteByDefectIds($defectIds);
      
      $defectVersionMapper = new Project_Model_DefectVersionMapper();
      $defectVersionMapper->deleteByDefectIds($defectIds);
      
      $db->delete(array(
        'id IN(?)' => $defectIds
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
  
  public function start(Application_Model_Defect $defect)
  {
    $defect->setStatus(Application_Model_DefectStatus::IN_PROGRESS);
    return $this->changeStatus($defect, array(
      Application_Model_DefectStatus::OPEN,
      Application_Model_DefectStatus::REOPEN
    ));
  }
  
  public function finish(Application_Model_Defect $defect)
  {
    $defect->setStatus(Application_Model_DefectStatus::FINISHED);
    return $this->changeStatus($defect, array(
      Application_Model_DefectStatus::OPEN,
      Application_Model_DefectStatus::REOPEN,
      Application_Model_DefectStatus::IN_PROGRESS
    ));
  }
  
  public function changeStatusToResolved(Application_Model_Defect $defect)
  {
    $defect->setStatus(Application_Model_DefectStatus::RESOLVED);
    return $this->changeStatus($defect, array(
      Application_Model_DefectStatus::INVALID
    ));
  }
  
  public function changeStatusToInvalid(Application_Model_Defect $defect)
  {
    $defect->setStatus(Application_Model_DefectStatus::INVALID);
    return $this->changeStatus($defect, array(
      Application_Model_DefectStatus::RESOLVED
    ));
  }
  
  public function changeStatusToSuccess(Application_Model_Defect $defect)
  {
    $defect->setStatus(Application_Model_DefectStatus::SUCCESS);
    return $this->changeStatus($defect, array(
      Application_Model_DefectStatus::FAIL
    ));
  }
  
  public function changeStatusToFail(Application_Model_Defect $defect)
  {
    $defect->setStatus(Application_Model_DefectStatus::FAIL);
    return $this->changeStatus($defect, array(
      Application_Model_DefectStatus::SUCCESS
    ));
  }
  
  private function _changeWhitComment(Application_Model_Defect $defect, array $data, array $where = array())
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $where['id = ?'] = $defect->getId();
    
    try
    {
      $adapter->beginTransaction();
      $db->update($data, $where);
      
      $commentContent = Utils_Text::unicodeTrim($defect->getExtraData('comment'));

      if (!empty($commentContent))
      {
        $comment = new Application_Model_Comment();
        $comment->setContent($commentContent);
        $comment->setUserObject($defect->getAssigner());
        $comment->setSubjectId($defect->getId());
        $comment->setSubjectType(Application_Model_CommentSubjectType::DEFECT);
        $commentMapper = new Project_Model_CommentMapper();

        if ($commentMapper->add($comment) === false)
        {
          throw new Exception('[AssignDefect] Comment adding is failed');
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
  
  public function assign(Application_Model_Defect $defect)
  {
    return $this->_changeWhitComment($defect, array(
      'assignee_id' => $defect->getAssigneeId(),
      'modify_date' => date('Y-m-d H:i:s')
    ));
  }
  
  public function resolve(Application_Model_Defect $defect)
  {
    return $this->_changeWhitComment($defect, array(
      'assignee_id' => $defect->getAssigneeId(),
      'modify_date' => date('Y-m-d H:i:s'),
      'status'      => Application_Model_DefectStatus::RESOLVED
   ), array(
      'status IN(?)' => array(
        Application_Model_DefectStatus::OPEN,
        Application_Model_DefectStatus::REOPEN,
        Application_Model_DefectStatus::IN_PROGRESS,
        Application_Model_DefectStatus::FINISHED,
        Application_Model_DefectStatus::INVALID
    )));
  }
  
  public function isInvalid(Application_Model_Defect $defect)
  {
    return $this->_changeWhitComment($defect, array(
      'assignee_id' => $defect->getAssigneeId(),
      'modify_date' => date('Y-m-d H:i:s'),
      'status'      => Application_Model_DefectStatus::INVALID
    ), array(
      'status IN(?)' => array(
        Application_Model_DefectStatus::OPEN,
        Application_Model_DefectStatus::REOPEN,
        Application_Model_DefectStatus::IN_PROGRESS,
        Application_Model_DefectStatus::FINISHED,
        Application_Model_DefectStatus::RESOLVED
    )));
  }
  
  public function reopen(Application_Model_Defect $defect)
  {
    return $this->_changeWhitComment($defect, array(
      'assignee_id' => $defect->getAssigneeId(),
      'modify_date' => date('Y-m-d H:i:s'),
      'status'      => Application_Model_DefectStatus::REOPEN
    ), array(
      'status IN(?)' => array(
        Application_Model_DefectStatus::RESOLVED,
        Application_Model_DefectStatus::INVALID,
        Application_Model_DefectStatus::SUCCESS,
        Application_Model_DefectStatus::FAIL
    )));
  }
  
  public function close(Application_Model_Defect $defect)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array(
      'status'        => $defect->getStatusId(),
      'modify_date'   => date('Y-m-d H:i:s')
    );
    
    $where = array(
      'id = ?'            => $defect->getId(),
      'status NOT IN(?)'  => array(
        Application_Model_DefectStatus::SUCCESS,
        Application_Model_DefectStatus::FAIL,
      )
    );

    try
    {
      $adapter->beginTransaction();
      $db->update($data, $where);

      $defectEnvironmentMapper = new Project_Model_DefectEnvironmentMapper();
      $defectEnvironmentMapper->save($defect);
      
      $defectVersionMapper = new Project_Model_DefectVersionMapper();
      $defectVersionMapper->save($defect);
      
      $commentContent = Utils_Text::unicodeTrim($defect->getExtraData('comment'));
      
      if (!empty($commentContent))
      {
        $comment = new Application_Model_Comment();
        $comment->setContent($commentContent);
        $comment->setUserObject($defect->getAssigner());
        $comment->setSubjectId($defect->getId());
        $comment->setSubjectType(Application_Model_CommentSubjectType::DEFECT);
        $commentMapper = new Project_Model_CommentMapper();

        if ($commentMapper->add($comment) === false)
        {
          throw new Exception('[EndDefect] Comment adding is failed');
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
  
  public function getByTask(Application_Model_Task $task)
  {
    $rows = $this->_getDbTable()->getByTask($task->getId());
    
    if (empty($rows))
    {
      return array();
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_Defect($row);
    }
    
    return $list;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request, Application_Model_Project $project)
  {
    return $this->_getDbTable()->getAllAjax($request, $project->getId())->toArray();
  }
  
  public function getByOrdinalNoForAjax(Application_Model_Defect $defect, Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getByOrdinalNoForAjax($defect->getOrdinalNo(), $project->getId());
    
    if (empty($row))
    {
      return false;
    }

    return $defect->setDbProperties($row->toArray());
  }
  
  public function getForViewAjax(Application_Model_Defect $defect, Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getForViewAjax($defect->getId(), $project->getId());
    
    if (empty($row))
    {
      return false;
    }

    $defect->setStatus($row['status']);
    return $row->toArray();
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
      $list[$row['id']] = new Application_Model_Defect($row);
    }

    return $list;
  }
}