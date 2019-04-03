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
class Project_Model_TestDefectMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TestDefectDbTable';

  public function add(Application_Model_TestDefect $testDefect)
  {
    $db = $this->_getDbTable();

    $data = array(
      'task_test_id'   => $testDefect->getTaskTest()->getId(),
      'defect_id'      => $testDefect->getDefect()->getId(),
      'bug_tracker_id' => $testDefect->getBugTrackerId()
    );

    try
    {
      $db->insert($data);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }

  public function delete(Application_Model_TestDefect $testDefect)
  {
    $where = array(
      'id = ?' => $testDefect->getId(),
    );

    try
    {
      $this->_getDbTable()->delete($where);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }

  public function getForViewByTaskTestDefect(Application_Model_TaskTest $taskTest, Application_Model_Defect $defect)
  {
    $row = $this->_getDbTable()->getForViewByTaskTestIdDefectId($taskTest->getId(), $defect->getId());

    if (null === $row)
    {
      return false;
    }
    $data = $row->toArray();

    $testDefect = new Application_Model_TestDefect();
    $testDefect->setId($data['id']);
    $testDefect->setTaskTestObject(new Application_Model_TaskTest(array('id' => $data['task_test_id'])));
    $testDefect->setDefectObject(new Application_Model_Defect(array('id' => $data['defect_id'])));
    return $testDefect;
  }

}