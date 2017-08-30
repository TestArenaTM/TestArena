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
class Administration_Model_ProjectActiveSettingsMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_ProjectActiveSettingsDbTable';

  public function save(Application_Model_Project $project)
  {
    $db = $this->_getDbTable();
    
    if (count($project->getActiveSettings()) > 0)
    {
      $adapter = $db->getAdapter();

      $data = array();
      $values = implode(',', array_fill(0, count($project->getActiveSettings()), '(?, ?)'));

      foreach ($project->getActiveSettings() as $setting)
      {
        $data[] = $project->getId();
        $data[] = $setting->getId();
      }

      $db->delete(array('project_id = ?' => $project->getId()));
      $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (project_id, project_setting_id) VALUES '.$values);
      return $statement->execute($data);
    }
    else
    {
      $db->delete(array('project_id = ?' => $project->getId()));
      return true;
    }
  }
}