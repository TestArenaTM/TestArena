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
class Project_Model_AttachmentMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_AttachmentDbTable';

  public function saveTest(Application_Model_Test $test)
  {
    $db = $this->_getDbTable();
    $attachmentIds = $test->getExtraData('attachmentIds');
    
    $db->delete(array(
      'subject_id = ?' => $test->getId(),
      'type = ?'       => Application_Model_AttachmentType::TEST_ATTACHMENT
    ));
    
    if (count($attachmentIds) > 0)
    {
      $adapter = $db->getAdapter();
      $date = date('Y-m-d H:i:s');
      $data = array();
      $values = implode(',', array_fill(0, count($attachmentIds), '(?, ?, ?, ?)'));

      foreach ($attachmentIds as $attachmentId)
      {
        $data[] = $attachmentId;
        $data[] = $test->getId();
        $data[] = Application_Model_AttachmentType::TEST_ATTACHMENT;
        $data[] = $date;
      }
      $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (file_id, subject_id, type, create_date) VALUES '.$values);
      return $statement->execute($data);
    }
    
    return true;
  }

  public function saveTask(Application_Model_Task $task)
  {
    $db = $this->_getDbTable();
    $attachmentIds = $task->getExtraData('attachmentIds');

    $db->delete(array(
      'subject_id = ?' => $task->getId(),
      'type = ?'       => Application_Model_AttachmentType::TASK_ATTACHMENT
    ));
    
    if (count($attachmentIds) > 0)
    {
      $adapter = $db->getAdapter();
      $date = date('Y-m-d H:i:s');
      $data = array();
      $values = implode(',', array_fill(0, count($attachmentIds), '(?, ?, ?, ?)'));

      foreach ($attachmentIds as $attachmentId)
      {
        $data[] = $attachmentId;
        $data[] = $task->getId();
        $data[] = Application_Model_AttachmentType::TASK_ATTACHMENT;
        $data[] = $date;
      }
      
      $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (file_id, subject_id, type, create_date) VALUES '.$values);
      return $statement->execute($data);
    }

    return true;
  }
  
  public function saveGroupForTasks(array $attachments)
  {
    $db = $this->_getDbTable();
    
    if (count($attachments) > 0)
    {
      $adapter = $db->getAdapter();
      $values = implode(',', array_fill(0, count($attachments), '(?, ?, ?, ?)'));

      foreach ($attachments as $attachment)
      {
        $data[] = $attachment->getFile()->getId();
        $data[] = $attachment->getSubjectId();
        $data[] = Application_Model_AttachmentType::TASK_ATTACHMENT;
        $data[] = $attachment->getCreateDate();
      }
      
      $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (file_id, subject_id, type, create_date) VALUES '.$values);
      return $statement->execute($data);
    }
    
    return true;
  }

  public function saveDefect(Application_Model_Defect $defect)
  {
    $db = $this->_getDbTable();
    $attachmentIds = $defect->getExtraData('attachmentIds');

    $db->delete(array(
      'subject_id = ?' => $defect->getId(),
      'type = ?'       => Application_Model_AttachmentType::DEFECT_ATTACHMENT
    ));
    
    if (count($attachmentIds) > 0)
    {
      $adapter = $db->getAdapter();
      $date = date('Y-m-d H:i:s');
      $data = array();
      $values = implode(',', array_fill(0, count($attachmentIds), '(?, ?, ?, ?)'));

      foreach ($attachmentIds as $attachmentId)
      {
        $data[] = $attachmentId;
        $data[] = $defect->getId();
        $data[] = Application_Model_AttachmentType::DEFECT_ATTACHMENT;
        $data[] = $date;
      }
      
      $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (file_id, subject_id, type, create_date) VALUES '.$values);
      return $statement->execute($data);
    }
    
    return true;
  }
  
  public function save4Project(Application_Model_Attachment $attachment)
  {
    $data = array(
      'file_id'     => $attachment->getFile()->getId(),
      'subject_id'  => $attachment->getSubjectId(),
      'type'        => $attachment->getTypeId(),
      'create_date' => date('Y-m-d H:i:s')
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
  
  public function getForProject(Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getForProject($project->getId());

    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_Attachment($row);
    }
    
    return $list;
  }

  public function delete(Application_Model_Attachment $attachment)
  {
    $this->_getDbTable()->delete(array(
      'id = ?' => $attachment->getId()
    ));
  }

  public function deleteByFileIds(array $fileIds)
  {
    $this->_getDbTable()->delete(array(
      'file_id IN(?)' => $fileIds
    ));
  }

  public function deleteByTask(Application_Model_Task $task)
  {
    $this->_getDbTable()->delete(array(
      'subject_id = ?' => $task->getId(),
      'type = ?' => Application_Model_AttachmentType::TASK_ATTACHMENT
    ));
  }

  public function deleteByTaskIds(array $taskIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $taskIds,
      'type = ?' => Application_Model_AttachmentType::TASK_ATTACHMENT
    ));
  }

  public function deleteByDefect(Application_Model_Defect $defect)
  {
    $this->_getDbTable()->delete(array(
      'subject_id = ?' => $defect->getId(),
      'type = ?' => Application_Model_AttachmentType::DEFECT_ATTACHMENT
    ));
  }

  public function deleteByDefectIds(array $defectIds)
  {
    $this->_getDbTable()->delete(array(
      'subject_id IN(?)' => $defectIds,
      'type = ?' => Application_Model_AttachmentType::DEFECT_ATTACHMENT
    ));
  }
}