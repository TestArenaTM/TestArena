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
class Zend_View_Helper_DefectUrl extends Zend_View_Helper_Abstract
{
  public function defectUrl($id, $bugTracker)
  {
    if ($id > 0 && empty($id))
    {
      return '';
    }
    
    if ($bugTracker instanceof Application_Model_BugTrackerJira)
    {
      return trim($bugTracker->getUrl(), '/').'/browse/'.$bugTracker->getProjectKey().'-'.$id;
    }
    elseif ($bugTracker instanceof Application_Model_BugTrackerMantis)
    {
      return trim($bugTracker->getUrl(), '/').'/view.php?id='.$id;
    }
    
    return $this->view->projectUrl(array('id' => $id), 'defect_view');
  }
}