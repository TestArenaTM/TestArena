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
class Zend_View_Helper_DefectHistory extends Zend_View_Helper_Abstract
{
  public function defectHistory(Application_Model_History $history)
  {
    switch ($history->getTypeId())
    {
      case Application_Model_HistoryType::CREATE_DEFECT:
        if ($history->getUser()->getId() == $history->getField1())
        {
          return $this->view->generalT('utworzył(a) defekt');
        }
        else
        {
          return $this->view->generalT('utworzył(a) defekt i przypisał(a) go użytkownika ASSIGNEE', array(
            'assignee' => $this->_getNiceAssignee($history)
          ));
        }
      case Application_Model_HistoryType::ADD_TASK_TO_DEFECT:
        $task = $this->_getTask($history);
        return $this->view->generalT('dodał(a) powiązanie zgłoszenia z zadaniem TASK', array(
          'task' => '<a href="'. $this->view->projectUrl(array('id' => $task->getId()), 'task_view') .'">'. $task->getName() .'</a>'
        ));

      case Application_Model_HistoryType::DELETE_TASK_FROM_DEFECT:
        $task = $this->_getTask($history);

        return $this->view->generalT('usunął(a) powiązanie zgłoszenia z zadaniem TASK', array(
          'task' => '<a href="'. $this->view->projectUrl(array('id' => $task->getId()), 'task_view') .'">'. $task->getName() .'</a>'
        ));

      case Application_Model_HistoryType::DELETE_TEST_FROM_DEFECT:
        $test = $this->_getTest($history);
        return $this->view->generalT('usunął/ęła test TEST ze zgłoszenia', array(
          'test' => '<a href="'.$this->view->projectUrl(array('id' => $test->getId()), $this->view->testViewRouteName($test)).'">'.$test->getName().'</a>',
        ));

      case Application_Model_HistoryType::ASSIGN_DEFECT:
        if ($history->getUser()->getId() == $history->getField1())
        {
          return $this->view->generalT('przypisał(a) defekt do siebie');
        }
        else
        {
          return $this->view->generalT('przypisał(a) defekt do użytkownika ASSIGNEE', array(
            'assignee' => $this->_getNiceAssignee($history)
          ));
        }
        
      case Application_Model_HistoryType::CHANGE_DEFECT:
        return $this->view->generalT('zmienił(a) defekt');
        
      case Application_Model_HistoryType::CHANGE_AND_ASSIGN_DEFECT:
        if ($history->getUser()->getId() == $history->getField1())
        {
          return $this->view->generalT('zmienił(a) defekt i przypisał(a) go do siebie');
        }
        else
        {
          return $this->view->generalT('zmienił(a) defekt i przypisał(a) go do użytkownika ASSIGNEE', array(
            'assignee' => $this->_getNiceAssignee($history)
          ));
        }
      
      case Application_Model_HistoryType::CHANGE_DEFECT_STATUS:
        $defectStatus = $this->_getNiceDefectStatus($history);
        if ($history->getField2() != null)
        {
          $assignee = $this->_getNiceAssignee($history);
          if ($history->getUser()->getId() == $history->getField2()) {
            return $this->view->generalT('zmienił(a) status defektu na DEFECT_STATUS i przypisał(a) go do siebie', array(
              'defectStatus' => $defectStatus,
              'assignee' => $assignee
            ));
          }
          return $this->view->generalT('zmienił(a) status defektu na DEFECT_STATUS i przypisał(a) go do użytkownika ASSIGNEE', array(
            'defectStatus' => $defectStatus,
            'assignee' => $assignee
          ));
        }
        return $this->view->generalT('zmienił(a) status defektu na DEFECT_STATUS', array(
          'defectStatus' => $defectStatus
        ));
      case Application_Model_HistoryType::DELETE_TASK_TEST_FROM_DEFECT:
        $taskAndTaskTest = $this->_getTaskAndTaskTest($history);
        /** @var Application_Model_TaskTest $taskTest */
        $taskTest = $taskAndTaskTest['taskTest'];
        /** @var Application_Model_Task $task */
        $task = $taskAndTaskTest['task'];
        /** @var Application_Model_Test $test */
        $test = $taskTest->getTest();

        $testView = $test->getName();
        if ($test->getStatus()->getId() != Application_Model_TestStatus::DELETED)
        {
          $testView = '<a href="'.$this->view->projectUrl(array('id' => $taskTest->getId()), $this->view->taskTestViewRouteName($test)).'">'.$test->getName().'</a>';
        }

        $taskView = '<a href="'. $this->view->projectUrl(array('id' => $task->getId()), 'task_view') .'">'.$task->getTitle().'</a>';

        // usunął/ęła powiązanie zadania TASK z testem TEST
        return $this->view->generalT('usunął(a) powiązanie zgłoszenia z testem TEST w zadaniu TASK', array(
          'test' => $testView,
          'task' => $taskView
        ));

      case Application_Model_HistoryType::ADD_TASK_TEST_TO_DEFECT:
        $taskAndTaskTest = $this->_getTaskAndTaskTest($history);
        /** @var Application_Model_TaskTest $taskTest */
        $taskTest = $taskAndTaskTest['taskTest'];
        /** @var Application_Model_Task $task */
        $task = $taskAndTaskTest['task'];
        /** @var Application_Model_Test $test */
        $test = $taskTest->getTest();

        $testView = $test->getName();
        if ($test->getStatus()->getId() != Application_Model_TestStatus::DELETED)
        {
          $testView = '<a href="'.$this->view->projectUrl(array('id' => $taskTest->getId()), $this->view->taskTestViewRouteName($test)).'">'.$test->getName().'</a>';
        }

        $taskView = '<a href="'. $this->view->projectUrl(array('id' => $task->getId()), 'task_view') .'">'.$task->getTitle().'</a>';

        return $this->view->generalT('dodał(a) powiązanie zgłoszenia z testem TEST w zadaniu TASK', array(
          'test' => $testView,
          'task' => $taskView
        ));

      case Application_Model_HistoryType::ADD_TEST_TO_DEFECT:
        $taskAndTest = $this->_getTaskAndTest($history);
        /** @var Application_Model_Test $test */
        $test = $taskAndTest['test'];
        /** @var Application_Model_Task $task */
        $task = $taskAndTest['task'];

        $testView = $test->getName();
        if ($test->getStatus()->getId() != Application_Model_TestStatus::DELETED)
        {
          $testView = '<a href="'.$this->view->projectUrl(array('id' => $task->getId()), $this->view->testViewRouteName($test)).'">'.$test->getName().'</a>';
        }

        $taskView = '<a href="'. $this->view->projectUrl(array('id' => $task->getId()), 'task_view') .'">'.$task->getTitle().'</a>';
        return $this->view->generalT('dodał(a) powiązanie zgłoszenia z testem TEST w zadaniu TASK', array(
          'test' => $testView,
          'task' => $taskView
        ));

    }

    return '';
  }
  
  private function _getNiceAssignee(Application_Model_History $history)
  {
    return '<strong title=\"'.$history->getExtraData('data2').'\">'.$history->getExtraData('data1').'</strong>';
  }
  
  private function _getNiceDefectStatus(Application_Model_History $history)
  {
    return '<strong>'.$this->view->statusT(new Application_Model_DefectStatus($history->getField1()), 'DEFECT').'</strong>';
  }

  private function _getTest(Application_Model_History $history)
  {
    $test = new Application_Model_Test();
    $test->setId($history->getField1());
    $test->setType($history->getExtraData('data1'));
    $test->setName($history->getExtraData('data2'));
    return $test->setStatus($history->getExtraData('data3'));
  }

  private function _getTask(Application_Model_History $history)
  {
    $task = new Application_Model_Test();
    $task->setId($history->getField1());
    $task->setName($history->getExtraData('data1'));
    return $task;
  }

  private function _getTaskAndTaskTest(Application_Model_History $history)
  {
    $task = new Application_Model_Task();
    $task->setId($history->getField1());
    $task->setTitle($history->getExtraData('data4'));
    $task->setStatus($history->getExtraData('data5'));

    $test = new Application_Model_Test();
    if (!empty($history->getExtraData('data2')))
    {
      $test->setType($history->getExtraData('data1'));
      $test->setName($history->getExtraData('data2'));
      $test->setStatus($history->getExtraData('data3'));
    }

    $taskTest = new Application_Model_TaskTest();
    $taskTest->setId($history->getField2());
    $taskTest->setTestObject($test);

    return array('task' => $task, 'taskTest' => $taskTest);
  }

  private function _getTaskAndTest(Application_Model_History $history)
  {
    $task = new Application_Model_Task();
    $task->setId($history->getField1());
    $task->setTitle($history->getExtraData('data4'));
    $task->setStatus($history->getExtraData('data5'));

    $test = new Application_Model_Test();
    if (!empty($history->getExtraData('data2')))
    {
      $test->setType($history->getExtraData('data1'));
      $test->setName($history->getExtraData('data2'));
      $test->setStatus($history->getExtraData('data3'));
    }

    $taskTest = new Application_Model_TaskTest();
    $taskTest->setId($history->getField2());
    $taskTest->setTestObject($test);

    return array('task' => $task, 'taskTest' => $taskTest);
  }
}