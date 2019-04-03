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
class Report_ProjectController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
    $this->_checkAccess(Application_Model_RoleAction::REPORT_GENERATE, true);
  }
  
  public function indexAction()
  {
    $projectMapper = new Report_Model_ProjectMapper();
    $this->_setTranslateTitle();
    $this->view->project = $projectMapper->getOne($this->getRequest());
  }
  
  public function exportAction()
  {
    $projectMapper = new Report_Model_ProjectMapper();
    $project = $projectMapper->getOne($this->getRequest());
    
    $t = new Custom_Translate();
    $fileName = _TEMP_PATH.DIRECTORY_SEPARATOR.Utils_Text::generateToken().'.csv';

    $writer = new Utils_File_Writer_Table_Csv($fileName, array(
      'allTasks'        => $t->translate('Wszystkie zadania'),
      'openTasks'       => $t->translate('Zadania niewykonane'),
      'closedTasks'     => $t->translate('Zadania zakończone'),
      'successTasks'    => $t->translate('Zadania zakończone powodzeniem'),
      'failedTasks'     => $t->translate('Zadania zakończone niepowodzeniem'),
      'inProgressTasks' => $t->translate('Zadania w toku'),
      'suspendedTasks'  => $t->translate('Zadania zawieszone'),
      'progress'        => $t->translate('Postęp').'[%]'
    ));
    
    $row = $project->getAllExtraData();
    $row['progress'] = $this->getHelper('Percent')->run($project->getExtraData('allTasks'), $project->getExtraData('closedTasks'));
    $writer->write($row);    
    $writer->close();
    
    $download = new Utils_Download($fileName, $t->translate('fileName', array('project' => $this->_project->getName())).'.csv');
    $download->save();
    exit();
  }
}