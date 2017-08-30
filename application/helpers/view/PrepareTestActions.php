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
class Zend_View_Helper_PrepareTestActions extends Zend_View_Helper_Abstract
{
  public function prepareTestActions(Application_Model_Test $test, array $userPermissions = array(), Application_Model_TestUserPermission $testUserPermission = null)
  {
    if (null === $testUserPermission)
    {
      $testUserPermission = new Application_Model_TestUserPermission($test, $this->view->authUser, $userPermissions);
    }
    
    $actions = array();
    
    //$actions[] = array('url' => $this->view->projectUrl(array('id' => $test->getId()), 'test_forward_to_execute'), 'text' => 'Przekaż do wykonania');
    
    if ($test->getStatusId() == Application_Model_TestStatus::ACTIVE
        && ($testUserPermission->isEditPermission() || $testUserPermission->isDeletePermission()))
    {
      //$actions[] = null;
      $actions[] = array('url' => $this->view->projectUrl(array('id' => $test->getId()), $this->view->testEditRouteName($test)), 'text' => 'Edytuj');
      $actions[] = array('url' => $this->view->projectUrl(array('id' => $test->getId()), $this->view->testAddRouteName($test)), 'text' => 'Klonuj');
      $actions[] = array('class' => 'j_delete_test', 'url' => $this->view->projectUrl(array('id' => $test->getId()), 'test_delete'), 'text' => 'Usuń');
    }
    
    
    return $actions;
  }
}
