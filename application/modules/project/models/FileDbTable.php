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
class Project_Model_FileDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'file';  
  
  public function getAllByTest($testId)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension',
        'path'
      ))
      ->join(array('a' => 'attachment'), 'a.file_id = f.id', array())
      ->where('a.subject_id = ?', $testId)
      ->where('a.type = ?', Application_Model_AttachmentType::TEST_ATTACHMENT)
      ->order('a.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getAllByTask($taskId)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension',
        'path'
      ))
      ->join(array('a' => 'attachment'), 'a.file_id = f.id', array())
      ->where('a.subject_id = ?', $taskId)
      ->where('a.type = ?', Application_Model_AttachmentType::TASK_ATTACHMENT)
      ->order('a.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function getAllByDefect($taskId)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension',
        'path'
      ))
      ->join(array('a' => 'attachment'), 'a.file_id = f.id', array())
      ->where('a.subject_id = ?', $taskId)
      ->where('a.type = ?', Application_Model_AttachmentType::DEFECT_ATTACHMENT)
      ->order('a.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}