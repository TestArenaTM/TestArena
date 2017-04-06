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
class Administration_Model_BugTrackerMantisMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_BugTrackerMantisDbTable';

  private function _getProjectId(Application_Model_BugTrackerMantis $bugTrackerMantis)
  {
    $soap = new Zend_Soap_Client(trim($bugTrackerMantis->getUrl(), '/').'/api/soap/mantisconnect.php?wsdl');
    $id = $soap->mc_project_get_id_from_name($bugTrackerMantis->getUserName(), $bugTrackerMantis->getPassword(), $bugTrackerMantis->getProjectName());
    $bugTrackerMantis->setProjectId($id);
  }
  
  public function add(Application_Model_BugTrackerMantis $bugTrackerMantis)
  {
    $this->_getProjectId($bugTrackerMantis);
    
    $data = array(
      'user_name'     => $bugTrackerMantis->getUserName(),
      'password'      => $bugTrackerMantis->getPassword(),
      'project_id'    => $bugTrackerMantis->getProjectId(),
      'project_name'  => $bugTrackerMantis->getProjectName(),
      'url'           => $bugTrackerMantis->getUrl()
    );

    return $this->_getDbTable()->insert($data);
  }
  
  public function save(Application_Model_BugTrackerMantis $bugTrackerMantis)
  {
    $this->_getProjectId($bugTrackerMantis);
    
    $data = array(
      'user_name'     => $bugTrackerMantis->getUserName(),
      'password'      => $bugTrackerMantis->getPassword(),
      'project_id'    => $bugTrackerMantis->getProjectId(),
      'project_name'  => $bugTrackerMantis->getProjectName(),
      'url'           => $bugTrackerMantis->getUrl()
    );

    $this->_getDbTable()->update($data, array('id = ?' => $bugTrackerMantis->getId()));
  }
}