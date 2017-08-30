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
class Zend_View_Helper_TaskHistory extends Zend_View_Helper_Abstract
{
  public function taskHistory(Application_Model_History $history)
  {
    switch ($history->getTypeId())
    {
      case Application_Model_HistoryType::CREATE_TASK:
        if ($history->getUser()->getId() == $history->getField1())
        {
          return $this->view->generalT('utworzył(a) zadanie');
        }
        else
        {
          return $this->view->generalT('utworzył(a) zadanie i przypisał(a) je do użytkownika ASSIGNEE', array(
            'assignee' => $this->_getNiceAssignee($history)
          ));
        }
        
      case Application_Model_HistoryType::ASSIGN_TASK:
        if ($history->getUser()->getId() == $history->getField1())
        {
          return $this->view->generalT('przypisał(a) zadanie do siebie');
        }
        else
        {
          return $this->view->generalT('przypisał(a) zadanie do użytkownika ASSIGNEE', array(
            'assignee' => $this->_getNiceAssignee($history)
          ));
        }
        
      case Application_Model_HistoryType::CHANGE_TASK:
        return $this->view->generalT('zmienił(a) zadanie');
        
      case Application_Model_HistoryType::CHANGE_AND_ASSIGN_TASK:
        if ($history->getUser()->getId() == $history->getField1())
        {
          return $this->view->generalT('zmienił(a) zadanie i przypisał(a) je do siebie');
        }
        else
        {
          return $this->view->generalT('zmienił(a) zadanie i przypisał(a) je do użytkownika ASSIGNEE', array(
            'assignee' => $this->_getNiceAssignee($history)
          ));
        }
      
      case Application_Model_HistoryType::CHANGE_TASK_STATUS:
        return $this->view->generalT('zmienił(a) status zadania na TASK_STATUS', array(
          'taskStatus' => $this->_getNiceTaskStatus($history)
        ));
      
      case Application_Model_HistoryType::ADD_TEST_TO_TASK:
        $test = $this->_getTest($history);
        $testParam = $test->getName();
        
        if ($test->getStatusId() != Application_Model_TestStatus::DELETED)
        {
          $testParam = '<a href="'.$this->view->projectUrl(array('id' => $test->getId()), $this->view->testViewRouteName($test)).'">'.$testParam.'</a>';
        }
        
        return $this->view->generalT('dodał(a) test TEST do zadania', array('test' => $testParam));
      
      case Application_Model_HistoryType::RESOLVE_TEST:
        $test = $this->_getTest($history);
        $testParam = $test->getName();
        
        if ($test->getStatusId() != Application_Model_TestStatus::DELETED)
        {
          $testParam = '<a href="'.$this->view->projectUrl(array('id' => $test->getId()), $this->view->testViewRouteName($test)).'">'.$testParam.'</a>';
        }
        
        return $this->view->generalT('rozwiązał(a) test TEST ze statusem STATUS', array(
          'test'   => $testParam,
          'status' => $history->getExtraData('data4')
        ));
      
      case Application_Model_HistoryType::CHANGE_TEST_STATUS:
        $test = $this->_getTest($history);
        $testParam = $test->getName();
        
        if ($test->getStatusId() != Application_Model_TestStatus::DELETED)
        {
          $testParam = '<a href="'.$this->view->projectUrl(array('id' => $test->getId()), $this->view->testViewRouteName($test)).'">'.$testParam.'</a>';
        }
        
        return $this->view->generalT('zmienił(a) status testu TEST na status STATUS', array(
          'test'   => $testParam,
          'status' => $history->getExtraData('data4')
        ));
        
      case Application_Model_HistoryType::DELETE_TEST_FROM_TASK:
        $test = $this->_getTest($history);
        $testParam = $test->getName();
        
        if ($test->getStatusId() != Application_Model_TestStatus::DELETED)
        {
          $testParam = '<a href="'.$this->view->testViewRouteName($test).'">'.$testParam.'</a>';
        }
        
        return $this->view->generalT('usunął/ęła test TEST Z zadania', array(
          'test' => '<a href="'.$this->view->projectUrl(array('id' => $test->getId()), $this->view->testViewRouteName($test)).'">'.$testParam.'</a>'
        ));
        
      case Application_Model_HistoryType::ADD_DEFECT_TO_TASK:
        $defect = $this->_getDefect($history);
        
        $testParam = '<a href="'.$this->view->projectUrl(array('id' => $defect->getId(), 'projectId' => $defect->getProject()->getId()), 'defect_view').'">'.$defect->getTitle().'</a>';
        
        return $this->view->generalT('dodał(a) defekt DEFEKT do zadania', array('defekt' => $testParam));
        
      case Application_Model_HistoryType::DELETE_DEFECT_FROM_TASK:
        $defect = $this->_getDefect($history);
        
        $testParam = '<a href="'.$this->view->projectUrl(array('id' => $defect->getId(), 'projectId' => $defect->getProject()->getId()), 'defect_view').'">'.$defect->getTitle().'</a>';
        
        return $this->view->generalT('usunoł/eła defekt DEFEKT z zadania', array('defekt' => $testParam));
    }
  
    return '';
  }
  
  private function _getNiceAssignee(Application_Model_History $history)
  {
    return '<strong title=\"'.$history->getExtraData('data2').'\">'.$history->getExtraData('data1').'</strong>';
  }
  
  private function _getNiceTaskStatus(Application_Model_History $history)
  {
    return '<strong>'.$this->view->statusT(new Application_Model_TaskStatus($history->getField1()), 'TASK').'</strong>';
  }
  
  private function _getTest(Application_Model_History $history)
  {
    $test = new Application_Model_Test();
    $test->setId($history->getField1());
    $test->setType($history->getExtraData('data1'));
    $test->setName($history->getExtraData('data2'));
    return $test->setStatus($history->getExtraData('data3'));
  }
  
  private function _getDefect(Application_Model_History $history)
  {
    $bugTrackerType = $history->getExtraData('data1');
    
    switch ($bugTrackerType)
    {
      case Application_Model_BugTrackerType::INTERNAL:
        $defect = new Application_Model_Defect();
    }
    
    $defect->setId($history->getField1());
    $defect->setTitle($history->getExtraData('data2'));
    
    return $defect->setProject('id', $history->getExtraData('data3'));
  }
}