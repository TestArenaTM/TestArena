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
class Project_Model_FileMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_FileDbTable';
  
  public function getById(Application_Model_File $file)
  {
    $row = $this->_getDbTable()->getById($file->getId());
    
    if ($row === null)
    {
      return false;
    }
    
    return $file->setDbProperties($row->toArray());
  }
  
  public function getBasicListBySubpath($path)
  {
    $rows = $this->_getDbTable()->getBasicListBySubpath($path);
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $file = new Application_Model_File($row);
      $list[$file->getFullName()] = $file;
    }
    
    return $list;
  }
  
  public function exists(Application_Model_File $file)
  {
    return $this->_getDbTable()->exists($file->getName(), $file->getExtension(), $file->getSubpath()) > 0;
  }
  
  public function add(Application_Model_File $file)
  {
    $data = array(
      'project_id'    => $file->getProject()->getId(),
      'name'          => $file->getName(),
      'extension'     => $file->getExtension(),
      'subpath'       => $file->getSubpath(),
      'create_date'   => $file->getCreateDate(),
      'description'   => $file->getDescription()
    );
    
    if ($file->IsTemporary())
    {
      $data['remove_date'] = $file->getRemoveDate();
    }
    
    try
    {
      $file->setId($this->_getDbTable()->insert($data));
      return $file->getId() > 0;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function deleteById(Application_Model_File $file)
  {
    $this->_getDbTable()->delete(array('id = ?' => $file->getId()));
  }
  
  public function deleteByIds(array $ids, $permanently = false)
  {
    if (count($ids))
    {
      $db = $this->_getDbTable();
      $adapter = $db->getAdapter();
      
      try
      {
        $adapter->beginTransaction();
        
        if ($permanently)
        {
          $attachmentMapper = new Project_Model_AttachmentMapper();
          $attachmentMapper->deleteByFileIds($ids);
        }
        
        $this->_getDbTable()->delete(array('id IN(?)' => $ids));
        
        return $adapter->commit();
      }
      catch (Exception $e)
      {
        Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
        $adapter->rollBack();
        return false;
      }
    }
  }
  
  public function getListByIds(array $ids)
  {
    $rows = $this->_getDbTable()->getListByIds($ids);
    
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
  
  public function getListConstainingSubpath($subpath)
  {
    $rows = $this->_getDbTable()->getListConstainingSubpath($subpath);

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
  
  public function rename(Application_Model_File $file, Application_Model_File $newFile)
  {
    return $this->_getDbTable()->update(array('name' => $newFile->getName()), array('id = ?' => $file->getId()));
  }
  
  public function renameDirectory($subpath, $newsubpath)
  {
    $db = $this->_getDbTable();
    $rows = $db->getSubpathListConstainingSubpath($subpath);

    if ($rows === null)
    {
      return false;
    }

    foreach ($rows->toArray() as $row)
    {
      $db->update(array(
          'subpath' => str_replace($subpath, $newsubpath, $row['subpath'])
        ), array(
          'subpath = ?' => $row['subpath']
        ));
    }   
  }
  
  public function getListByTest(Application_Model_Test $test)
  {
    $rows = $this->_getDbTable()->getListByTest($test->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = new Application_Model_File($row);
    }
    
    return $list;
  }
  
  public function getListByTask(Application_Model_Task $task)
  {
    $rows = $this->_getDbTable()->getListByTask($task->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = new Application_Model_File($row);
    }
    
    return $list;
  }
  
  public function getListByDefect(Application_Model_Defect $defect)
  {
    $rows = $this->_getDbTable()->getListByDefect($defect->getId());
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();

    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = new Application_Model_File($row);
    }
    
    return $list;
  }
}