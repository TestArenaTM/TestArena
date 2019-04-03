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
class Zend_View_Helper_ShowDefectStatusIcon extends Zend_View_Helper_Abstract
{
  public function showDefectStatusIcon(Application_Model_Defect $defect)
  {
    $color = '';
    switch ($defect->getStatusId())
    {
      case Application_Model_DefectStatus::OPEN:
        $color = $defect->getProject()->getOpenStatusColor();
        break;

      case Application_Model_DefectStatus::REOPEN:
        $color = $defect->getProject()->getReopenStatusColor();
        break;

      case Application_Model_DefectStatus::IN_PROGRESS:
        $color = $defect->getProject()->getInProgressStatusColor();
        break;

      case Application_Model_DefectStatus::FINISHED:
        $color = $defect->getProject()->getClosedStatusColor();
        break;

      case Application_Model_DefectStatus::INVALID:
        $color = $defect->getProject()->getInvalidStatusColor();
        break;

      case Application_Model_DefectStatus::RESOLVED:
        $color = $defect->getProject()->getResolvedStatusColor();
        break;

      case Application_Model_DefectStatus::SUCCESS:
        $color = $defect->getProject()->getResolvedStatusColor();
        break;
      case Application_Model_DefectStatus::FAIL:
        $color = $defect->getProject()->getInvalidStatusColor();
        break;

    }

    $style = '';
    if ($color !== '') {
      $style = 'style="background: '. $color .'"';
    }

    return '<span '. $style .' class="statusIcon defectStatus_'.$defect->getStatus().'" title="'.$this->view->statusT($defect->getStatus(), 'DEFECT').'"></span>';
  }
}