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
class Report_Model_ReleaseMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Report_Model_ReleaseDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setCurrentPageNumber($request->getParam('page'));
    
    $list = array();
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      $release = new Application_Model_Release();
      $list[] = $release->setDbProperties($row);
    }
    
    return array($list, $paginator);
  }
  
  public function getAllForExport(Zend_Controller_Request_Abstract $request)
  {
    $rows = $this->_getDbTable()->getAllForExport($request);
    
    if ($rows === null)
    {
      return false;
    }
    
    return $rows->toArray();
  }
}