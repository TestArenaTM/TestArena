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
class Administration_Model_TaskRunMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_TaskRunDbTable';
  
  public function getForExportDefectsByProject(Application_Model_Project $project)
  {
    try
    {
      $rows = $this->_getDbTable()->getForExportDefectsByProject($project->getId());
    
      if ($rows === null)
      {
        return false;
      }
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return $rows->toArray();
  }
  
  public function exportDefectsByProject(Application_Model_Project $project)
  {
    /**** Obiekt File ****/
    $file = new Application_Model_File();
    $file->setIsTemporary(true);
    $file->setDates(1);
    $file->setName($project->getName().'_defects_'.'_'.date('Ymd_His', strtotime($file->getCreateDate())));
    $file->setExtension('csv');
    $file->setPath(_FILE_UPLOAD_DIR.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR);

    $rows = $this->getForExportDefectsByProject($project);      
    $csvFile = new Utils_File_Writer_Table_Csv($file->getFullPath(true), $this->_prepareCsvColumnsToExport($rows));
    $csvFile->writeMany($rows);
    $csvFile->close();

    /**** Dodawanie pliku ****/
    $fileMapper = new Application_Model_FileMapper();
    
    if ($fileMapper->add($file))
    {
      $project->setExtraData('fileId', $file->getId());      
      return true;
    }

    @unlink($csvFile->getFileName());
    return false;
  }
  
  private function _prepareCsvColumnsToExport(array $rows)
  {
    $columns = array();
    
    foreach ($rows as $row)
    {
      foreach (array_keys($row) as $columnName)
      {
        $columns[$columnName] = $columnName;
      }
      
      break;
    }
    
    return $columns;
  }
}