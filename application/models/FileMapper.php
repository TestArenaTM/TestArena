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
class Application_Model_FileMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Application_Model_FileDbTable';
  
  public function add(Application_Model_File $file)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();
      
      $data = array(
        'name'        => $file->getName(),
        'extension'   => $file->getExtension(),
        'subpath'     => $file->getSubpath(),
        'create_date' => $file->getCreateDate(),
        'description' => $file->getDescription()
      );

      if ($file->getProject() !== null && $file->getProject()->getId() > 0)
      {
        $data['project_id'] = $file->getProject()->getId();
      }
      
      if ($file->isTemporary())
      {
        $data['remove_date'] = $file->getRemoveDate();
      }

      $file->setId($db->insert($data));
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
    
    return $file->getId() > 0;
  }
  
  public function getById(Application_Model_File $file)
  {
    $row = $this->_getDbTable()->getById($file->getId());
    
    if ($row === null)
    {
      return false;
    }
    
    return $file->setDbProperties($row->toArray());
  }
}