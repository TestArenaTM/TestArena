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
class Dashboard_IndexController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
  }
  
  public function indexAction()
  {
    $request = $this->getRequest();
    $request->setParam('userId', $this->_user->getId());
    
    if ($this->_isActiveProject())
    {
      $request->setParam('projectId', $this->_project->getId());
    }
    $taskMapper = new Dashboard_Model_TaskMapper();
    $messageMapper = new Dashboard_Model_MessageMapper();
    $this->getHelper('HTMLPurifier')->run();
    
    $allTasksCnt = $taskMapper->getAllCnt($request);
    
    $allTasksAssigned2YouCntByStatus = $taskMapper->getAllAssigned2YouCntByStatus($request);
    
    $this->_setTranslateTitle();
    $this->view->latestNotClosedTasksAssigned2You = $taskMapper->getLimitLatestNotClosedAssigned2You($request, 5);
    
    $this->view->overdueTasks = $taskMapper->getLimitOverdue($request, 5);
    $this->view->numberOfOverdueTasks = $taskMapper->getNumberOfOverdue($request);
    
    $this->view->latestMessages = $messageMapper->getLimitLatest($this->_user, 5);
    $this->view->numberOfUnreadMessages = $messageMapper->getNumberOfUnread($this->_user);
    
    //Project data
    $this->view->allTasksAssigned2YouCnt = $allTasksAssigned2YouCntByStatus['all'];
    $this->view->allTasksAssigned2YouCntPrct = ($allTasksCnt > 0) ? number_format($allTasksAssigned2YouCntByStatus['all']/$allTasksCnt * 100, 1): 0;
    
    $this->view->allOpenTasksAssigned2YouCnt = $allTasksAssigned2YouCntByStatus['open'];
    $this->view->allOpenTasksAssigned2YouCntPrct = ($allTasksAssigned2YouCntByStatus['all'] > 0) ? number_format($allTasksAssigned2YouCntByStatus['open']/$allTasksAssigned2YouCntByStatus['all'] * 100, 1): 0;
    
    $this->view->allInProgressTasksAssigned2YouCnt = $allTasksAssigned2YouCntByStatus['inProgress'];
    $this->view->allInProgressTasksAssigned2YouCntPrct = ($allTasksAssigned2YouCntByStatus['all'] > 0) ? number_format($allTasksAssigned2YouCntByStatus['inProgress']/$allTasksAssigned2YouCntByStatus['all'] * 100, 1): 0;
    
    $this->view->allClosedTasksAssigned2YouCnt = $allTasksAssigned2YouCntByStatus['closed'];
    $this->view->allClosedTasksAssigned2YouCntPrct = ($allTasksAssigned2YouCntByStatus['all'] > 0) ? number_format($allTasksAssigned2YouCntByStatus['closed']/$allTasksAssigned2YouCntByStatus['all'] * 100, 1): 0;
    
    $this->view->projectTaskChartDataJson = $this->_prepareData4TaskChart($allTasksAssigned2YouCntByStatus);
  }
  
  private function _prepareData4TaskChart(array $allTasksAssigned2YouCntByStatus)
  {
    $t = new Custom_Translate();
    
    $dataTable = array(
      'cols' => array(
        array('type' => 'string', 'label' => 'Zadania'),
        array('type' => 'number', 'label' => 'Ilość')
      )
    );
    
    $dataTable['rows'] = array(
      array(
        'c' => array (
          array('v' => $t->translate('Otwarte zadania')),
          array('v' => $allTasksAssigned2YouCntByStatus['open'])
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Zadania w toku')),
          array('v' => $allTasksAssigned2YouCntByStatus['inProgress'])
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Zamknięte zadania')),
          array('v' => $allTasksAssigned2YouCntByStatus['closed'])
        )
      ),
    );
    
    return json_encode($dataTable);
  }
}