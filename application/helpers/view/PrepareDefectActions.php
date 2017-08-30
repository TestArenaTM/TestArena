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
class Zend_View_Helper_PrepareDefectActions extends Zend_View_Helper_Abstract
{
  public function prepareDefectActions(Application_Model_Defect $defect, array $userPermissions = array(), Application_Model_DefectUserPermission $defectUserPermission = null)
  {
    if (null === $defectUserPermission)
    {
      $defectUserPermission = new Application_Model_DefectUserPermission($defect, $this->view->authUser, $userPermissions);
    }
    
    $actions = array();
    
    if ($defectUserPermission->isChangeStatusPermission())
    {
      if (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::OPEN, 
                                                 Application_Model_DefectStatus::REOPEN, 
                                                 Application_Model_DefectStatus::IN_PROGRESS)))
      {
        if (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::OPEN, 
                                                   Application_Model_DefectStatus::REOPEN)))
        {
          $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_start'), 'text' => 'Rozpocznij'); 
        }

        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_finish'), 'text' => 'Zakończ');
        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_resolve'), 'text' => 'Rozwiąż');
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_is_invalid'), 'text' => 'Jest niepoprawny');
        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_close'), 'text' => 'Zamknij');
      }
      elseif (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::FINISHED)))
      {
        /* FINISHED */
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_resolve'), 'text' => 'Rozwiąż');
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_is_invalid'), 'text' => 'Jest niepoprawny');
        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_close'), 'text' => 'Zamknij');
      }
      elseif (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::RESOLVED, 
                                                     Application_Model_DefectStatus::INVALID)))
      {
        /* RESOLVED, INVALID */
        if ($defect->getStatusId() == Application_Model_DefectStatus::RESOLVED)
        {
          $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_change_to_invalid'), 'text' => 'Zmień na "Niepoprawny"');
        }
        else
        {
          $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_change_to_resolved'), 'text' => 'Zmień na "Rozwiązany"');
        }

        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_reopen'), 'text' => 'Otwórz ponownie');
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_close'), 'text' => 'Zamknij');
      }
      elseif (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::SUCCESS)))
      {
        /* SUCCESS */
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_reopen'), 'text' => 'Otwórz ponownie');
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_change_to_fail'), 'text' => 'Zmień na "Zamknięty negatywnie"');
      }
      elseif (in_array($defect->getStatusId(), array(Application_Model_DefectStatus::FAIL)))
      {
        /* FAIL */
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_reopen'), 'text' => 'Otwórz ponownie');
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_change_to_success'), 'text' => 'Zmień na "Zamknięty pozytywnie"');
      }
    }

    if (!in_array($defect->getStatusId(), array(Application_Model_DefectStatus::SUCCESS,
                                                Application_Model_DefectStatus::FAIL)))
    {
      /* NOT CLOSED */
      
      if ($defectUserPermission->isAssignPermission())
      {
        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_assign'), 'text' => 'Przypisz');
      
        if ($defect->getAssigneeId() != $this->view->authUser->getId())
        {
          $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_assign_to_me'), 'text' => 'Przypisz do mnie');
        }
      }
      
      if ($defectUserPermission->isEditPermission())
      {
        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_edit'), 'text' => 'Edytuj');
      }  
      
      if ($defectUserPermission->isDeletePermission())
      {
        $actions[] = null;
        $actions[] = array('url' => $this->view->projectUrl(array('id' => $defect->getId()), 'defect_delete'), 'text' => 'Usuń', 'class' => 'j_delete_defect');
      }  
    }
    
    return $actions;
  }
}