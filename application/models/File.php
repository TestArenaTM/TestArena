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
class Application_Model_File extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'create_date'   => 'createDate',
    'remove_date'   => 'removeDate',    
  );
  
  private $_id          = null;
  private $_project     = null;
  private $_name        = null;
  private $_extension   = null;
  private $_subpath     = null;
  private $_createDate  = null;
  private $_removeDate  = null;
  private $_description = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  function getProject()
  {
    return $this->_project;
  }
    
  public function getName()
  {
    return $this->_name;
  }
  
  public function getExtension()
  {
    return $this->_extension;
  }
  
  public function getFullName()
  {
    return strlen($this->getExtension()) > 0 ? $this->getName().'.'.$this->getExtension() : $this->getName();
  }
  
  // Przykładowy wygląd ścieżki: \temp\ \74\
  public function getDefaultSubpath()
  {
    $path = DIRECTORY_SEPARATOR;
    
    if ($this->_project !== null && $this->_project->getId() > 0)
    {
      $path .= $this->_project->getId();
    }
    else
    {
      $path .= 'temp';
    }
    
    return $path;
  }
  
  // Przykładowy wygląd ścieżki: C:\www\testarena\files
  public function getMainPath()
  {
    return _FILE_UPLOAD_DIR.$this->getDefaultSubpath();
  }
  
  // Przykładowy wygląd ścieżki: \74\images\
  public function getSubpath()
  {
    return $this->_subpath;
  }
  
  // Przykładowy wygląd ścieżki: C:\www\testarena\files\74\images\fileName.ext
  public function getFullPath($systemNameEncoding = false)
  {
    $fullPath = $this->getMainPath().$this->getSubpath().$this->getFullName();
    
    if ($systemNameEncoding)
    {
      $encoding = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'CP1250' : 'ISO-8859-2';
      $fullPath = iconv('UTF-8', $encoding, $fullPath);
    }

    return $fullPath;
  }
  
  // Przykładowy wygląd ścieżki: C:\www\testarena\files\74\images\newFileName
  public function getNewFullPath($fileName)
  {
    return $this->getMainPath().$this->getSubpath().$fileName;
  }
  
  public function getCreateDate()
  {
    return $this->_createDate;
  }
  
  public function getRemoveDate()
  {
    return $this->_removeDate;
  }
  
  public function getDescription()
  {
    return $this->_description;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }

  public function setProject($propertyName, $propertyValue)
  {
    if (null === $this->_project)
    {
      $this->_project = new Application_Model_Project(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getProject()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setProjectObject(Application_Model_Project $project)
  {
    $this->_project = $project;
    return $this;
  }
  
  public function setName($name, $filter = false)
  {
    if ($filter)
    {
      $name = str_replace(array('<', '>', ':', '"', '/', '\\', '|', '*', '?'), '_', $name);
    }

    $this->_name = $name;
    return $this;
  }
  
  public function setExtension($extension)
  {
    $this->_extension = $extension;
    return $this;
  }
  
  public function setSubpath($subpath = false)
  {
    $this->_subpath = $subpath === false ? DIRECTORY_SEPARATOR : $subpath;
    return $this;
  }
  
  public function setSubpathByFullPath($fullPath)
  {
    $info = pathinfo($fullPath);
    $this->setName($info['filename']);
    $this->setExtension($info['extension']);
    $mainPathLength = strlen($this->getMainPath());
    $this->setSubpath(substr($info['dirname'], $mainPathLength, strlen($info['dirname']) - $mainPathLength).DIRECTORY_SEPARATOR);
  }

  public function setCreateDate($date)
  {
    $this->_createDate = $date;
    return $this;
  }
  
  public function setRemoveDate($date)
  {
    if ($date != '0000-00-00 00:00:00')
    {
      $this->_removeDate = $date;
    }
    return $this;
  }

  public function setDescription($description)
  {
    $this->_description = $description;
    return $this;
  } 
  
  public function setDates($removeDayOffset = false)
  {
    $now = time();
    $this->setCreateDate(date('Y-m-d H:i:s', $now));
    $removeDate = '0000-00-00 00:00:00';
    
    if ($removeDayOffset !== false)
    {
      $day = 60 * 60 * 24;
      $removeDate = date('Y-m-d H:i:s', $now + ($removeDayOffset * $day));
    }
    
    $this->setRemoveDate($removeDate);
  }
  // </editor-fold> 
  
  public function isTemporary()
  {
    return $this->_project === null;
  }
  
  public function isSupportedImage()
  {
    try
    {    
      $image = new Utils_Image($this->getFullPath(true));
    }
    catch (Utils_Image_Exception $e)
    {
      return false;
    }
    
    return true;
  }
}