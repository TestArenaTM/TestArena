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
class Project_FileBrowserController extends Custom_Controller_Action_Application_Project_Abstract
{
  const DIRECTORY_SEPARATOR = '|';
  
  private $_fileSystemEncoding;
  private $_projectPath;
  private $_projectPathLength;

  public function preDispatch()
  {
    parent::preDispatch();
    $this->_fileSystemEncoding = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'CP1250' : 'ISO-8859-2';
    $this->_projectPath = _FILE_UPLOAD_DIR.DIRECTORY_SEPARATOR.$this->_project->getId();
    $this->_projectPathLength = strlen($this->_projectPath);
  }
  
  private function _getValidFile()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->getRequest()->getParams()))
    {
      throw new Custom_404Exception();
    }
    
    $file = new Application_Model_File($idValidator->getFilteredValues());
    $fileMapper = new Project_Model_FileMapper();
    
    if ($fileMapper->getById($file) === false)
    {
      throw new Custom_404Exception();
    }
    
    return $file;
  }
  
  public function indexAction()
  {
    $this->_helper->layout->setLayout('empty');
    $this->view->directorySeparator = self::DIRECTORY_SEPARATOR;
    $this->view->mode = $this->getRequest()->getParam('mode', 0);
  }
  
  public function directoryListAjaxAction()
  {
    $treeList = $this->_getDirectoriesTree($this->_getFullPath(DIRECTORY_SEPARATOR));
    echo json_encode($treeList);
    exit();
  }
  
  private function _getDirectoriesTree($directory)
  {
    $list = array();
    
    foreach (glob($directory.'*', GLOB_ONLYDIR) as $directory)
    {
      $directory .= DIRECTORY_SEPARATOR;
      $info = pathinfo($directory);
      $info['dirname'] = str_replace(array('/', '\\'), self::DIRECTORY_SEPARATOR, $info['dirname']);
      $info['dirname'] = trim(substr($info['dirname'], $this->_projectPathLength,  mb_strlen($info['dirname'], 'UTF-8') - $this->_projectPathLength), self::DIRECTORY_SEPARATOR)."\n";
      $info['dirname'] = self::DIRECTORY_SEPARATOR.trim($info['dirname']);

      foreach ($info as $k => $v)
      {
        $info[$k] = iconv($this->_fileSystemEncoding, 'UTF-8', $v);
      }

      $info['items'] = $this->_getDirectoriesTree($directory); 
      $list[] = $info;
    }

    return $list;
  }
  
  public function fileListAjaxAction()
  {
    $subpath = $this->_getClearPath();
    $fsPath = iconv('UTF-8', $this->_fileSystemEncoding, $subpath);
    $list = array('directories' => array(), 'files' => array());
    $fileMapper = new Project_Model_FileMapper();
    $files = $fileMapper->getBasicListBySubpath($subpath);

    foreach (glob($this->_getFullPath($fsPath).'*') as $fullPath)
    {
      if (is_dir($fullPath))
      {
        $info = pathinfo($fullPath);
        $list['directories'][] = iconv($this->_fileSystemEncoding, 'UTF-8', $info['basename']);
      }
      else
      {
        $info = pathinfo($fullPath);
        $info['basename'] = iconv($this->_fileSystemEncoding, 'UTF-8', $info['basename']);
        
        if (array_key_exists($info['basename'], $files))
        {
          $file = $files[$info['basename']];
          unset($files[$info['basename']]);
          
          $list['files'][] = array(
            'id'        => $file->getId(),
            'fullname'  => $file->getFullName(),
            'name'      => $file->getName(),
            'extension' => $file->getExtension()
          );
        }
        else
        {
          @unlink($fullPath);
        }
      }
    }

    if (count($files) > 0)
    {
      $ids = array();
      
      foreach ($files as $file)
      {
        $ids[] = $file->getId();
      }
      
      $fileMapper->deleteByIds($ids, true);
    }
    
    echo json_encode($list);
    exit();
  }
  
  private function _addFile($srcFullPath, $subpath, $fileName)
  {
    $info = pathinfo($fileName);
    $file = new Application_Model_File();
    $file->setDates();
    $file->setProjectObject($this->_project);
    $file->setName($info['filename']);
    $file->setExtension($info['extension']);
    $file->setSubpath($subpath);
    $filePath = $file->getFullPath(true);
    
    if (!in_array($file->getExtension(), array('jpg', 'jpeg', 'png', 'gif', 'doc', 'xls', 'csv', 'txt', 'xml', 'html', 'pdf')))
    {
      return 'FORBIDDEN_EXTENSION';
    }
    else if (file_exists($filePath))
    {
      return 'FILE_EXISTS';
    }
    
    move_uploaded_file($srcFullPath, $filePath);
    $fileMapper = new Project_Model_FileMapper();
    
    if (!$fileMapper->exists($file))
    {
      return $fileMapper->add($file);
    }

    return true;
  }
  
  public function uploadAction()
  {
    $subpath = $this->_getClearPath();

    if (!empty($subpath))
    {
      if (isset($_FILES['file']))
      {
        $result = array(
          'status'    => 'OK',
          'fileNames' => array(
            'error'   => array(),
            'exists'  => array()
          )
        );
        
        if (!is_array($_FILES['file']['name'])) //single file
        {
          $status = $this->_addFile($_FILES['file']['tmp_name'], $subpath, $_FILES['file']['name']);

          if ($status === false)
          {echo 'ERROR';
            $result['status'] = 'ERROR';
            $result['fileNames']['error'][] = $_FILES['file']['name'];
          }
          elseif ($status === 'FILE_EXISTS')
          {
            $result['status'] = 'ERROR';
            $result['fileNames']['exists'][] = $_FILES['file']['name'];
          }
          elseif ($status === 'FORBIDDEN_EXTENSION')
          {
            $result['status'] = 'ERROR';
            $result['fileNames']['forbiddenExtensions'][] = $_FILES['file']['name'];
          }
        }
        else  //Multiple files, file[]
        {
          $fileCount = count($_FILES['file']['name']);
          
          for ($i=0; $i < $fileCount; $i++)
          {
            $status = $this->_addFile($_FILES['file']['tmp_name'][$i], $subpath, $_FILES['file']['name'][$i]);
            
            if ($status === false)
            {
              $result['status'] = 'ERROR';
              $result['fileNames']['error'][] = $_FILES['file']['name'][$i];
            }
            elseif ($status == 'FILE_EXISTS')
            {
              $result['status'] = 'ERROR';
              $result['fileNames']['exists'][] = $_FILES['file']['name'][$i];
            }
          }
        }
        
        echo json_encode($result);
      }
    }
    
    exit();
  }
  
  public function removeFileAction()
  {
    $file = $this->_getValidFile();

    if ($file->getExtraData('attachmentCount') > 0)
    {
      echo 'FILE_IS_ATTACHMENT';
    }
    else
    {
      $fileMapper = new Project_Model_FileMapper();
      $fileMapper->deleteById($file);
      @unlink($file->getFullPath(true));
      echo 'OK';
    }
    
    exit();
  }
  
  private function _removeDirectory($subpath)
  {
    $result = 'OK';

    if (!empty($subpath))
    {
      $fullPath = iconv('UTF-8', $this->_fileSystemEncoding, $this->_getFullPath($subpath));
      
      if (file_exists($fullPath)) 
      {
        $fileMapper = new Project_Model_FileMapper();
        $files = $fileMapper->getListConstainingSubpath($subpath);

        if ($files !== false && count($files) > 0)
        {
          $fileInDirIsAttachment = false;
          $fileIds = array();
          
          foreach ($files as $file)
          {
            if ($file->getExtraData('attachmentCount') == 0)
            {
              $fileIds[] = $file->getId();
              @unlink($file->getFullPath(true));
            }
            else
            {
              $fileInDirIsAttachment = true;
            }
          }

          $fileMapper->deleteByIds($fileIds);
          
          if ($fileInDirIsAttachment)
          {
            $result = 'FILE_IN_DIR_IS_ATTACHMENT';
          }
        }

        $this->_removeDirectoryRecursive($fullPath);
      }
      else
      {
        $result = 'DIRECTORY_NOT_EXISTS';
      }
    }

    return $result;
  }
  
  private function _removeDirectoryRecursive($path)
  {
    if (is_dir($path))
    {
      $objects = scandir($path);
      
      foreach ($objects as $object)
      {
        if (is_dir($path.DIRECTORY_SEPARATOR.$object) && $object != '.' && $object != '..')
        {
          $this->_removeDirectoryRecursive($path.DIRECTORY_SEPARATOR.$object);
        }
      }
      
      @rmdir($path);
    }
  }
  
  public function removeDirectoryAction()
  {
    echo $this->_removeDirectory($this->_getClearPath());
    exit();
  }
  
  public function removeAction()
  {
    $request = $this->getRequest();
    $fileIds = $request->getPost('fileIds', array());
    $directories = $request->getPost('directories', array());
    $fileIdsToRemove = array();
    
    if (!is_array($fileIds))
    {
      $fileIds = array();
    }

    if (!is_array($directories))
    {
      $directories = array();
    }
    
    $result = true;
    
    if (!empty($fileIds))
    {
      $fileMapper = new Project_Model_FileMapper();
      $files = $fileMapper->getListByIds($fileIds);

      foreach ($files as $file)
      {
        if ($file->getExtraData('attachmentCount') > 0)
        {
          $result = false;
        }
        else
        {
          $fileIdsToRemove[] = $file->getId();
          @unlink($file->getFullPath(true));
        }
      }

      if (count($fileIdsToRemove))
      {
        $fileMapper->deleteByIds($fileIdsToRemove);
      }
    }

    foreach ($directories as $directory)
    {
      if ($this->_removeDirectory($this->_clearPath($directory)) != 'OK')
      {
        $result = false;
      }
    }
    
    echo $result ? 'OK' : 'SOME_FILES_ARE_ATTACHMENTS';
    exit();
  }
  
  public function renameAction()
  {
    $oldFile = $this->_getValidFile();

    $newFile = new Application_Model_File();
    $newFile->setProjectObject($this->_project);
    $newFile->setName(urldecode($this->getRequest()->getPost('newName')));
    $newFile->setSubpath($oldFile->getSubpath());
    $newFile->setExtension($oldFile->getExtension());

    if ($newFile->getName() === null)
    {
      echo 'FILE_NOT_EXISTS';
    }
    elseif (file_exists($newFile->getFullPath(true)))
    {
      echo 'DESTINATION_FILE_ALREADY_EXISTS';
    }
    else
    {
      $fileMapper = new Project_Model_FileMapper();
      $fileMapper->rename($oldFile, $newFile);
      rename($oldFile->getFullPath(true), $newFile->getFullPath(true));
      echo 'OK';
    }
    
    exit();
  }
  
  public function renameDirectoryAction()
  {
    $subpath = $this->_getClearPath();
    $newSubpath = $this->_getClearPath('newPath');

    if (!empty($subpath) && !empty($newSubpath))
    {
      $fullPath = iconv('UTF-8', $this->_fileSystemEncoding, $this->_getFullPath($subpath));
      $newFullPath = iconv('UTF-8', $this->_fileSystemEncoding, $this->_getFullPath($newSubpath));

      if (!file_exists($fullPath) || !is_dir($fullPath))
      {
        echo 'DIRECTORY_NOT_EXISTS';
      }
      elseif (file_exists($newFullPath))
      {
        echo 'DESTINATION_DIRECTORY_ALREADY_EXISTS';
      }
      else
      {
        $fileMapper = new Project_Model_FileMapper();
        $fileMapper->renameDirectory($subpath, $newSubpath);
        rename($fullPath, $newFullPath);
        echo 'OK';
      }
    }
    
    exit();
  }
  
  public function createDirectoryAction()
  {
    $path = $this->_getClearPath();

    if (!empty($path))
    {
      $path = iconv('UTF-8', $this->_fileSystemEncoding, $this->_getFullPath($path));

      if (!file_exists($path)) 
      {
        if (!@mkdir($path) || preg_match('/[<>]+/', $path))
        {
          echo 'DIRECTORY_NAME_IS_ICORRECT';
        }
        else
        {
          echo 'OK';
        }
      }
      else
      {
        echo 'DESTINATION_DIRECTORY_ALREADY_EXISTS';
      }
    }
    
    exit();
  }

  private function _getClearPath($pathName = 'path')
  {
    return $this->_clearPath(urldecode($this->getRequest()->getPost($pathName, self::DIRECTORY_SEPARATOR)));
  }
  
  private function _clearPath($path)
  {
    $path = str_replace(array('.', '\\', '/'), '', $path);
    $path = preg_replace('/['.self::DIRECTORY_SEPARATOR.self::DIRECTORY_SEPARATOR.']+/u', self::DIRECTORY_SEPARATOR, $path);
    $path = str_replace(self::DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
    return $path == DIRECTORY_SEPARATOR ? $path : $path.DIRECTORY_SEPARATOR;
  }
  
  private function _getFullPath($subPath)
  {
    $path = $this->_projectPath;

    if (!is_dir($path))
    {
      mkdir($path);
    }
    
    return $path.$subPath;
  }
}