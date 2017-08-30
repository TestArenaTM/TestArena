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
class IndexController extends Custom_Controller_Action_Application_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();
    $this->_helper->layout->setLayout('static');
  }
  
  public function indexAction()
  {
    $this->_setTranslateTitle();
  }
  
  public function runScriptAction()
  {
    //include _ROOT_DIR.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'createInternalBugTrackers.php';
    //include _ROOT_DIR.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'updateFiles.php';
    //include _ROOT_DIR.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'removeTasksWithDeletedStatus.php';
    //include _ROOT_DIR.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'removeDefectsWithDeletedStatus.php';
    exit();
  }
  
  public function activateFinishedProjectsScriptAction()
  {
    exec('php '._ROOT_DIR.'/scripts/activateFinishedProjects.php',$op);
    echo '<pre>';
    var_dump($op);
    echo '</pre>';
    exit;
  }
  
  public function chartAction()
  {
    $string = '{
  "cols": [
        {"id":"","label":"","pattern":"","type":"string"},
        {"id":"","label":"Zadania","pattern":"","type":"number"}
      ],
  "rows": [
        {"c":[{"v":"Otwarte zadania przypisane do mnie","f":null},{"v":7,"f":null}]},
        {"c":[{"v":"Zadania w toku przypisane do mnie","f":null},{"v":2,"f":null}]},
        {"c":[{"v":"Zamknięte zadania przypisane do mnie","f":null},{"v":2,"f":null}]}
      ]
} ';
	echo $string;
	exit;
  }
}