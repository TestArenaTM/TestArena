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
class Report_UserController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
    $this->_checkAccess(Application_Model_RoleAction::REPORT_GENERATE, true);
  }
  
  public function indexAction()
  {
    $this->_setTranslateTitle();
    $userMapper = new Report_Model_UserMapper();
    list($list, $paginator) = $userMapper->getAll($this->getRequest());
    $this->view->users = $list;
    $this->view->paginator = $paginator;
  }
  
  public function exportAllAction()
  {
    $userMapper = new Report_Model_UserMapper();
    $rows = $userMapper->getAllForExport($this->getRequest());
    
    $t = new Custom_Translate();
    $fileName = _TEMP_PATH.DIRECTORY_SEPARATOR.Utils_Text::generateToken().'.csv';
//var_dump($rows);die;
    $writer = new Utils_File_Writer_Table_Csv($fileName, array(
      'name'            => $t->translate('Użytkownik'),
      'releaseName'     => $t->translate('Wydanie'),
      'allTasks'        => $t->translate('Wszystkie zadania'),
      'openTasks'       => $t->translate('Zadania niewykonane'),
      'closedTasks'     => $t->translate('Zadania zakończone'),
      'successTasks'    => $t->translate('Zadania zakończone powodzeniem'),
      'failedTasks'     => $t->translate('Zadania zakończone niepowodzeniem'),
      'inProgressTasks' => $t->translate('Zadania w toku'),
      'suspendedTasks'  => $t->translate('Zadania zawieszone'),
      'progress'        => $t->translate('Postęp').'[%]'
    ));
    
    foreach ($rows as $row)
    {
      if ($row['releaseName'] === null)
      {
        $row['releaseName'] = $t->translate('defaultReleaseName', null, 'general');
      }
      
      $row['progress'] = $this->getHelper('Percent')->run($row['allTasks'], $row['closedTasks']);
      $writer->write($row);
    }   
    
    $writer->close();
    
    $download = new Utils_Download($fileName, $t->translate('fileName', array('project' => $this->_project->getName())).'.csv');
    $download->save();
    exit();
  }
}