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
    // Filter
    $filterForm = null;
    $mainLinkPart = '?assignee='.$this->_user->getId();
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
    $this->view->overdueTasksAssignedToMe = $taskMapper->getLimitOverdueAssignedToMe($this->_user, $project, $release, 5);
    $this->view->numberOfOverdueTasksAssignedToMe = $taskMapper->countOverdueAssignedToMe($this->_user, $project, $release);
    
    // Messages
    $this->getHelper('HTMLPurifier')->run();
    $this->view->latestMessages = $messageMapper->getLimitLatest($this->_user, 5);
    $this->view->numberOfUnreadMessages = $messageMapper->getNumberOfUnread($this->_user);
    
    // Statistics
    $numberOfTasks = $taskMapper->countAll($project, $release);
    $numberOfTasksAssignedToMeGroupedByStatus = $taskMapper->countAssignedToMeGroupedByStatus($this->_user, $project, $release);
    
    $this->view->numberOfTasksAssignedToMe = $numberOfTasksAssignedToMeGroupedByStatus['all'];
    $this->view->percentOfTasksAssignedToMe = ($numberOfTasks > 0) ? number_format($numberOfTasksAssignedToMeGroupedByStatus['all']/$numberOfTasks * 100, 1): 0;
    
    $this->view->numberOfOpenTasksAssignedToMe = $numberOfTasksAssignedToMeGroupedByStatus['open'];
    $this->view->percentOfOpenTasksAssignedToMe = ($numberOfTasksAssignedToMeGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedToMeGroupedByStatus['open']/$numberOfTasksAssignedToMeGroupedByStatus['all'] * 100, 1): 0;
    
    $this->view->numberOfReopenTasksAssignedToMe = $numberOfTasksAssignedToMeGroupedByStatus['reopen'];
    $this->view->percentOfReopenTasksAssignedToMe = ($numberOfTasksAssignedToMeGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedToMeGroupedByStatus['reopen']/$numberOfTasksAssignedToMeGroupedByStatus['all'] * 100, 1): 0;
    
    $this->view->numberOfInProgressTasksAssignedToMe = $numberOfTasksAssignedToMeGroupedByStatus['inProgress'];
    $this->view->percentOfInProgressTasksAssignedToMe = ($numberOfTasksAssignedToMeGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedToMeGroupedByStatus['inProgress']/$numberOfTasksAssignedToMeGroupedByStatus['all'] * 100, 1): 0;
    
    $this->view->numberOfClosedTasksAssignedToMe = $numberOfTasksAssignedToMeGroupedByStatus['closed'];
    $this->view->percentOfClosedTasksAssignedToMe = ($numberOfTasksAssignedToMeGroupedByStatus['all'] > 0) ? number_format($numberOfTasksAssignedToMeGroupedByStatus['closed']/$numberOfTasksAssignedToMeGroupedByStatus['all'] * 100, 1): 0;
    
    $this->view->json_numberOfTasksAssignedToMeGroupedByStatus = $this->_prepareData4TaskChart($numberOfTasksAssignedToMeGroupedByStatus);
  }
  
  private function _prepareData4TaskChart(array $numberOfTasksAssignedToMeGroupedByStatus)
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
          array('v' => $t->translate('Nowe zadania')),
          array('v' => $numberOfTasksAssignedToMeGroupedByStatus['open'])
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Ponownie otwarte zadania')),
          array('v' => $numberOfTasksAssignedToMeGroupedByStatus['reopen'])
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Zadania w toku')),
          array('v' => $numberOfTasksAssignedToMeGroupedByStatus['inProgress'])
        )
      ),
      array(
        'c' => array (
          array('v' => $t->translate('Zamknięte zadania')),
          array('v' => $numberOfTasksAssignedToMeGroupedByStatus['closed'])
        )
      ),
    );

    return json_encode($dataTable);
  }
}