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
class Zend_View_Helper_DefectStatusTag extends Zend_View_Helper_Abstract
{
  public function defectStatusTag($defectObject, $bugTracker)
  {
    if (empty($defectObject))
    {
      return '';
    }
    
    if ($bugTracker instanceof Application_Model_BugTrackerJira)
    {
      return '<strong id="'.$defectObject->getId().'" key="'.$defectObject->getExtraData('key').'" class="j_defectStatus smallLoader"></strong>';
    }
    elseif ($bugTracker instanceof Application_Model_BugTrackerMantis)
    {
      return '<strong id="'.$defectObject->getId().'" key="'.$defectObject->getExtraData('key').'" class="j_defectStatus smallLoader"></strong>';
    }
    //'<strong id="j_defectStatus_'.$defect->getNo().'" class="j_defectStatus"></strong>';
    return '<strong>'.$this->view->statusT($defectObject->getStatus(), 'DEFECT').'</strong>';
  }
}