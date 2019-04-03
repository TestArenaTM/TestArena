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
class Dashboard_IndexController extends Custom_Controller_Action_Application_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
  }
  
  private function _getFilterForm()
  {
    return new Dashboard_Form_Filter(array('action' => $this->_url(array(), 'index')));
  }
  
  public function indexAction()
  {
    $roleAcl = new Application_Model_RoleAcl();
    $isExtendingProjectStatistic = $roleAcl->isAccessAllowed(Application_Model_RoleAction::EXTENDING_PROJECT_STATISTIC, $this->_user);

    $onlyMe = true;
    if ($isExtendingProjectStatistic) {
      $onlyMe = true;
      $request = $this->getRequest();
      $type = $request->getParam('type');
      if ($type == 'project') {
        $onlyMe = false;
      }
    }
    
    // Filter
    $filterForm = null;
    $mainLinkPart = '?';
    if ($onlyMe) {
      $mainLinkPart = '?assignee='.$this->_user->getId();
    }
    $release = new Application_Model_Release();
    $release->setId(0);
    
    if ($this->_isActiveProject())
    {
      $project = $this->_project;
      $releaseMapper = new Project_Model_ReleaseMapper();
      $activeRelease = $releaseMapper->getActive($this->_project);
      
      if ($activeRelease !== null)
      {
        $request = $this->_getRequestForFilter(Application_Model_FilterGroup::DASHBOARD);
        $request->setParam('filterAction', 2);
        $this->view->activeRelease = $activeRelease;
        $filterForm = $this->_getFilterForm();

        if ($filterForm->isValid($request->getParams()))
        {
          if ($filterForm->getValue('onlyActiveRelease'))
          {
            $release = $activeRelease;
            $mainLinkPart .= '&release='.$release->getId();
          }
        }

        $this->_filterAction($filterForm->getValues());
      }
    }
    else
    {
      $project = new Application_Model_Project();
      $project->setId(0);
    }

    $this->view->release = $release;
    $this->view->mainLinkPart = $mainLinkPart;
    $this->view->filterForm = $filterForm;
    
    $taskMapper = new Dashboard_Model_TaskMapper();
    $messageMapper = new Dashboard_Model_MessageMapper();
    
    $this->_setTranslateTitle();

    // Last tasks
    $this->view->lastNotClosedTasksAssignedToMe = $taskMapper->getLimitLastNotClosedAssignedToMe($this->_user, $project, $release, 5);
    $this->view->numberOfNotClosedTasksAssignedToMe = $taskMapper->countNotClosedAssignedToMe($this->_user, $project, $release);
    
    // Overdue tasks
    $this->view->overdueTasksAssigned = $taskMapper->getLimitOverdueAssigned($this->_user, $project, $release, 5, $onlyMe);
    $this->view->numberOfOverdueTasksAssigned = $taskMapper->countOverdueAssigned($this->_user, $project, $release, $onlyMe);

    
    // Messages
    $this->getHelper('HTMLPurifier')->run();
    $this->view->latestMessages = $messageMapper->getLimitLatest($this->_user, 5);
    $this->view->numberOfUnreadMessages = $messageMapper->getNumberOfUnread($this->_user);
    
    // Statistics
    $numberOfTasks = $taskMapper->countAll($project, $release);
    $numberOfTasksAssignedGroupedByStatus = $taskMapper->countAssignedGroupedByStatus($this->_user, $project, $release, $onlyMe);

    $this->view->numberOfTasksAssigned = $numberOfTasksAssignedGroupedByStatus['all'];
    $this->view->percentOfTasksAssigned = ($numberOfTasks > 0) ? number_format($numberOfTasksAssignedGroupedByStatus['all']/$numberOfTasks * 100, 1): 0;
    
    $this->view->numberOfOpenTasksAssigned = $numberOfTasksAssignedGroupedByStatus['open'];
    $this->view->percentOfOpenTasksAssigned = ($numberOfTasksAssignedGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedGroupedByStatus['open']/$numberOfTasksAssignedGroupedByStatus['all'] * 100, 1): 0;
    
    $this->view->numberOfReopenTasksAssigned = $numberOfTasksAssignedGroupedByStatus['reopen'];
    $this->view->percentOfReopenTasksAssigned = ($numberOfTasksAssignedGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedGroupedByStatus['reopen']/$numberOfTasksAssignedGroupedByStatus['all'] * 100, 1): 0;
    
    $this->view->numberOfInProgressTasksAssigned = $numberOfTasksAssignedGroupedByStatus['inProgress'];
    $this->view->percentOfInProgressTasksAssigned = ($numberOfTasksAssignedGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedGroupedByStatus['inProgress']/$numberOfTasksAssignedGroupedByStatus['all'] * 100, 1): 0;
    
    $this->view->numberOfClosedTasksAssigned = $numberOfTasksAssignedGroupedByStatus['closed'];
    $this->view->percentOfClosedTasksAssigned = ($numberOfTasksAssignedGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedGroupedByStatus['closed']/$numberOfTasksAssignedGroupedByStatus['all'] * 100, 1): 0;

    $this->view->json_numberOfTasksAssignedGroupedByStatus = $this->_prepareData4TaskChart($numberOfTasksAssignedGroupedByStatus);
    $this->view->isExtendingProjectStatistic = $isExtendingProjectStatistic;
    $this->view->onlyMe = $onlyMe;
    $this->view->view = $this->_user;
    if ($this->_project !== null) {
      $this->view->openStatusColor = $this->_project->getOpenStatusColor();
      $this->view->inProgressStatusColor = $this->_project->getInProgressStatusColor();
      $this->view->reopenStatusColor = $this->_project->getReopenStatusColor();
      $this->view->closedStatusColor = $this->_project->getClosedStatusColor();
    }
  }

  /**
   * @param array $numberOfTasksAssignedGroupedByStatus
   * @return array
   */
  private function _calculatePercentsForRoundedValues(array $numberOfTasksAssignedGroupedByStatus)
  {
    $percents = array();
    foreach ($numberOfTasksAssignedGroupedByStatus as $key => $value) {
      if ($key !== 'all' && $value != 0) {
        $percents[$key] = number_format(($value / $numberOfTasksAssignedGroupedByStatus['all']) * 100, 1, '.', ' ');
      }
    }

    $percentSum = array_sum($percents);
    if ($percentSum > 0) {
      if ($percentSum > 100) {
        $percents[$key] = $percents[$key] - ($percentSum - 100);
      }
      if ($percentSum < 100) {
        $percents[$key] = $percents[$key] + (100 - $percentSum);
      }
    }

    foreach ($numberOfTasksAssignedGroupedByStatus as $key => $value) {
      if (array_key_exists($key, $percents) === false) {
        $percents[$key] = 0;
      }
    }

    return $percents;
  }
  
  private function _prepareData4TaskChart(array $numberOfTasksAssignedGroupedByStatus)
  {
    $t = new Custom_Translate();
    
    $dataTable = array(
      'cols' => array(
        array('type' => 'string', 'label' => 'Zadania'),
        array('type' => 'number', 'label' => 'Ilość')
      )
    );

    $percents = $this->_calculatePercentsForRoundedValues($numberOfTasksAssignedGroupedByStatus);

    $dataTable['rows'] = array(
      array(
        'c' => array (
          array('v' => $t->translate('Nowe zadania') .' - '. $numberOfTasksAssignedGroupedByStatus['open']),
          array('v' => $numberOfTasksAssignedGroupedByStatus['open'], 'f' => $percents['open'] . '%')
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Ponownie otwarte zadania') .' - '. $numberOfTasksAssignedGroupedByStatus['reopen']),
          array('v' => $numberOfTasksAssignedGroupedByStatus['reopen'], 'f' => $percents['reopen'] . '%')
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Zadania w toku') .' - '. $numberOfTasksAssignedGroupedByStatus['inProgress']),
          array('v' => $numberOfTasksAssignedGroupedByStatus['inProgress'], 'f' => $percents['inProgress'] . '%'),
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Zamknięte zadania') .' - '. $numberOfTasksAssignedGroupedByStatus['closed']),
          array('v' => $numberOfTasksAssignedGroupedByStatus['closed'], 'f' => $percents['closed'] . '%')
        )
      ),
    );

    return json_encode($dataTable);
  }
}