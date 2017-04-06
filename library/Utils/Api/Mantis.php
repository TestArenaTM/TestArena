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
class Utils_Api_Mantis
{  
  static public function getProjectIdByName($url, $name, $userName = null, $password = null)
  {
    $soap = new Zend_Soap_Client(trim($url, '/').'/api/soap/mantisconnect.php?wsdl');
    return $soap->mc_project_get_id_from_name($userName, $password, $name);
  }
  
  static public function checkUserHaveAccessToProject($url, $name, $userName = null, $password = null)
  {
    $soap = new Zend_Soap_Client(trim($url, '/').'/api/soap/mantisconnect.php?wsdl');
    $projects = $soap->mc_projects_get_user_accessible($userName, $password);
    
    if (is_array($projects))
    {
      foreach ($projects as $project)
      {
        if ($project->name == $name)
        {
          return true;
        }
      }
    }
    
    return false;
  }
  
  static public function getIssueById($id, $url, $userName = null, $password = null)
  {
    $soap = new Zend_Soap_Client(trim($url, '/').'/api/soap/mantisconnect.php?wsdl');
    return $soap->mc_issue_get($userName, $password, $id);
  }
  
  static public function getIssueStatusById($id, $url, $userName = null, $password = null)
  {
    $soap = new Zend_Soap_Client(trim($url, '/').'/api/soap/mantisconnect.php?wsdl');
    $result = $soap->mc_issue_get($userName, $password, $id);
    return $result->status->name;
  }
  
  static public function getIssueSummaryAndStatusById($id, $url, $userName = null, $password = null)
  {
    $soap = new Zend_Soap_Client(trim($url, '/').'/api/soap/mantisconnect.php?wsdl');
    $result = $soap->mc_issue_get($userName, $password, $id);
    return array(
      'summary' => $result->summary,
      'status'  => $result->status->name
    );
  }
  
  static public function getIssueSummaryById($id, $url, $userName = null, $password = null)
  {
    $soap = new Zend_Soap_Client(trim($url, '/').'/api/soap/mantisconnect.php?wsdl');
    $result = $soap->mc_issue_get($userName, $password, $id);
    return $result->summary;
  }
}