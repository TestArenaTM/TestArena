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
        'is_temporary'  => $file->getIsTemporary(),
        'name'          => $file->getName(),
        'extension'     => $file->getExtension(),
        'path'          => $file->getPath(),
        'create_date'   => $file->getCreateDate()
      );
      
      if ($file->getIsTemporary())
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
  
  public function getIdByFullPath(Application_Model_File $file)
  {
    $id = $this->_getDbTable()->getIdByFullPath($file->getName(), $file->getExtension(), $file->getPath());
    
    if ($id > 0)
    {
      $file->setId($id);
      return true;
    }
    
    return false;
  }
  
  public function getByPath($path)
  {
    $rows = $this->_getDbTable()->getByPath($path);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $file = new Application_Model_File($row);
      
      if ($file->getId() > 0)
      {
        $list[] = $file;
      }
    }

    return $list;
  }
  
  public function attachmentExistsByFullPath(Application_Model_File $file)
  {
    return $this->_getDbTable()->attachmentExistsByFullPath($file->getName(), $file->getExtension(), $file->getPath()) > 0;
  }
  
  public function deleteByPaths(array $paths)
  {
    $this->_getDbTable()->delete(array('path IN(?)' => $paths));
  }
  
  public function deleteById(Application_Model_File $file)
  {
    $this->_getDbTable()->delete(array('id = ?' => $file->getId()));
  }
  
  public function deleteByIds(array $ids)
  {
    if (count($ids))
    {
      $this->_getDbTable()->delete(array('id IN(?)' => $ids));
    }
  }
  
  public function rename(Application_Model_File $file, Application_Model_File $newFile)
  {
    return $this->_getDbTable()->update(array('name' => $newFile->getName()), array('id = ?' => $file->getId()));
  }
  
  public function renameDirectory($path, $newPath)
  {
    $db = $this->_getDbTable();
    $rows = $db->getAllPathBySubPath($path);

    if ($rows === null)
    {
      return false;
    }

    foreach ($rows->toArray() as $row)
    {
      $db->update(array(
          'path' => str_replace($path, $newPath, $row['path'])
        ), array(
          'path = ?' => $row['path']
        ));
    }   
  }
  
  public function getForBrowserByPath($path)
  {
    $rows = $this->_getDbTable()->getForBrowserByPath($path);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['name'].'.'.$row['extension']] = $row['id'];
    }
    
    return $list;
  }
  
  public function getForBrowserById(Application_Model_File $file)
  {
    $row = $this->_getDbTable()->getForBrowserById($file->getId());
    
    if ($row === null)
    {
      return false;
    }
    
    return $file->setDbProperties($row->toArray());
  }
  
  public function getForBrowserByIds(array $ids)
  {
    $rows = $this->_getDbTable()->getForBrowserByIds($ids);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = new Application_Model_File($row);
    }
    
    return $list;
  }
}