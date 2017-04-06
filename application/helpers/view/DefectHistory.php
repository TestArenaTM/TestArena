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
          return $this->view->generalT('utworzył(a) defekt i przypisał(a) go użytkownikowi ASSIGNEE_NAME (ASSIGNEE_EMAIL)', array(
            'assigneeName'  => $history->getExtraData('field1Data1'),
            'assigneeEmail' => $history->getExtraData('field1Data2')
          ));
        }
        
      case Application_Model_HistoryType::CHANGE_DEFECT:
        if ($history->getUser()->getId() == $history->getField1())
        {
          return $this->view->generalT('zmienił(a) defekt');
        }
        else
        {
          return $this->view->generalT('zmienił(a) defekt i przypisał(a) go użytkownikowi ASSIGNEE_NAME (ASSIGNEE_EMAIL)', array(
            'assigneeName'  => $history->getExtraData('field1Data1'),
            'assigneeEmail' => $history->getExtraData('field1Data2')
          ));
        }
      
      case Application_Model_HistoryType::CHANGE_DEFECT_STATUS:
        return $this->view->generalT('zmienił(a) status defektu na DEFECT_STATUS', array(
          'defectStatus' => $this->view->statusT(new Application_Model_DefectStatus($history->getField1()), 'DEFECT')
        ));
    }
  
    return '';
  }
}