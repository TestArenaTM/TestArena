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
class Project_Model_CommentMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_CommentDbTable';

  public function getBySubjectAjax(Application_Model_Comment $comment, Application_Model_User $authUser)
  {
    $rows = $this->_getDbTable()->getBySubjectAjax($comment->getSubjectId(), $comment->getSubjectTypeId());

    if (null === $rows)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $user = new Application_Model_User();
      $user->setId($row['userId']);
      $row['avatarUrl'] = $user->getAvatarUrl(true);
      $row['content'] = nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8'));
      $row['isOwner'] = $user->getId() == $authUser->getId();
      $row['otherSubject'] = (bool)$row['otherSubject'];
      $list[] = $row;
    }

    return $list;
  }

  public function getByTaskAjax(Application_Model_Task $task, Application_Model_User $authUser, Zend_View_Interface $view)
  {
    $rows = $this->_getDbTable()->getByTask($task->getId());

    if (null === $rows)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $user = new Application_Model_User();
      $user->setId($row['userId']);
      $row['avatarUrl'] = $user->getAvatarUrl(true);
      $row['content'] = nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8'));
      $row['isOwner'] = $user->getId() == $authUser->getId();
      $row['otherSubject'] = (bool)$row['otherSubject'];
      
      if ($row['otherSubject'])
      {
        $test = new Application_Model_Test();
        $test->setId($row['testId']);
        $test->setType($row['testType']);
        $row['subjectUrl'] = $view->projectUrl(array('id' => $row['subject_id']), $view->taskTestViewRouteName($test));
      }
      
      unset($row['subject_id']);
      unset($row['testId']);
      unset($row['testType']);
      $list[] = $row;
    }

    return $list;
  }
  
  public function add(Application_Model_Comment $comment)
  {
    $data = array(
      'subject_id'    => $comment->getSubjectId(),
      'subject_type'  => $comment->getSubjectTypeId(),
      'user_id'       => $comment->getUser()->getId(),
      'status'        => Application_Model_CommentStatus::ACTIVE,
      'content'       => $comment->getContent(),
      'create_date'   => date('Y-m-d H:i:s')
    );

    try
    {
      $this->_getDbTable()->insert($data);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function save(Application_Model_Comment $comment)
  {
    $data = array(
      'content'     => $comment->getContent(),
      'modify_date' => date('Y-m-d H:i:s')
    );
    
    $where = array(
      'id = ?'      => $comment->getId(),
      'user_id = ?' => $comment->getUser()->getId(),
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
  
  public function delete(Application_Model_Comment $comment)
  {
    $data = array(
      'status' => Application_Model_CommentStatus::DELETED
    );
    
    $where = array(
      'id = ?'      => $comment->getId(),
      'user_id = ?' => $comment->getUser()->getId()
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
  
  public function deleteBySubject(Application_Model_Comment $comment)
  {
    $where = array(
      'subject_id = ?'    => $comment->getSubjectId(),
      'subject_type = ?'  => $comment->getSubjectTypeId()
    );

    try
    {
      $this->_getDbTable()->delete( $where);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }

  public function deleteByTask(Application_Model_Task $task)
  {
    $this->_getDbTable()->delete(array(
      'subject_id = ?' => $task->getId(),
      'subject_type = ?' => Application_Model_CommentSubjectType::TASK
    ));
  }

  public function deleteByTaskIds(array $taskIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $taskIds,
      'subject_type = ?' => Application_Model_CommentSubjectType::TASK
    ));
  }

  public function deleteByTaskTestIds(array $taskTestIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $taskTestIds,
      'subject_type = ?' => Application_Model_CommentSubjectType::TASK_TEST
    ));
  }

  public function deleteByDefect(Application_Model_Defect $defect)
  {
    $this->_getDbTable()->delete(array(
      'subject_id = ?' => $defect->getId(),
      'subject_type = ?' => Application_Model_CommentSubjectType::DEFECT
    ));
  }

  public function deleteByDefectIds(array $defectIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $defectIds,
      'subject_type = ?' => Application_Model_CommentSubjectType::DEFECT
    ));
  }
}