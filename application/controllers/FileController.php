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
class FileController extends Custom_Controller_Action_Application_Abstract
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
    $this->_projectPathLength = mb_strlen($this->_projectPath, 'UTF-8');
  }
  
  public function downloadAction()
  {
    $file = $this->_getValidFileData();
    $session = new Zend_Session_Namespace('FileDownload');
    $this->_setTranslateTitle();
    $this->_helper->layout->setLayout(isset($session->layout) ? $session->layout : 'default');
    $this->view->messages = isset($session->messages) ? $session->messages : array($file->getFullName());
    $this->view->file = $file;
    $this->view->backUrl = $this->_getBackUrl('file_dwonload', null, true);
  }
  
  public function downloadProcessAction()
  {
    $file = $this->_getValidFileData();
    $fullPath = iconv('UTF-8', $this->_fileSystemEncoding, $file->getFullPath());
    $download = new Utils_Download($fullPath, $file->getFullName());
    
    if ($download->save() === false)
    {
      throw new Custom_404Exception();
    }
    
    exit();
  }
  
  private function _getValidFile()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->getRequest()->getParams()))
    {
      throw new Custom_404Exception();
    }
    
    return new Application_Model_File($idValidator->getFilteredValues());
  }
  
  private function _getValidFileData()
  {
    $file = $this->_getValidFile();
    $fileMapper = new Application_Model_FileMapper();
    
    if ($fileMapper->getById($file) === false)
    {
      throw new Custom_404Exception();
    }
    
    return $file;
  }
  
  /* 
   * Browser  
   */
  public function browserAction()
  {
    $this->_helper->layout->setLayout('empty');
    $this->view->directorySeparator = self::DIRECTORY_SEPARATOR;
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
    $path = $this->_getClearPath('path');
    $fsPath = iconv('UTF-8', $this->_fileSystemEncoding, $path);
    $list = array('directories' => array(), 'files' => array());
    $fileMapper = new Application_Model_FileMapper();
    $fileIds = $fileMapper->getForBrowserByPath($this->_getFullPath($path));

    foreach (glob($this->_getFullPath($fsPath).'*') as $path)
    {
      if (is_dir($path))
      {
        $info = pathinfo($path);
        $list['directories'][] = iconv($this->_fileSystemEncoding, 'UTF-8', $info['basename']);
      }
      else
      {
        $info = pathinfo($path);
        $info['basename'] = iconv($this->_fileSystemEncoding, 'UTF-8', $info['basename']);
        
        $list['files'][] = array(
          'id'        => $fileIds[$info['basename']],
          'fullname'  => $info['basename'],
          'name'      => iconv($this->_fileSystemEncoding, 'UTF-8', $info['filename']),
          'extension' => iconv($this->_fileSystemEncoding, 'UTF-8', $info['extension'])
        );
      }
    }
    
    echo json_encode($list);
    exit();
  }
  
  private function _addFile($srcFullPath, $path, $fileName)
  {
    $info = pathinfo($fileName);
    $file = new Application_Model_File();
    $file->setDates();
    $file->setIsTemporary(false);
    $file->setName($info['filename']);
    $file->setExtension($info['extension']);
    $file->setPath($path);
    $filePath = iconv('UTF-8', $this->_fileSystemEncoding, $file->getFullPath());
    
    if (file_exists($filePath))
    {
      return 'FILE_EXISTS';
    }
    
    move_uploaded_file($srcFullPath, $filePath);
    $fileMapper = new Application_Model_FileMapper();
    
    if (!$fileMapper->getIdByFullPath($file))
    {
      return $fileMapper->add($file);
    }
    
    return true;
  }
  
  public function uploadAction()
  {
    $path = $this->_getClearPath();

    if (!empty($path))
    {
      $path = $this->_getFullPath($path);
        
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
          $status = $this->_addFile($_FILES['file']['tmp_name'], $path, $_FILES['file']['name']);
          
          if ($status === false)
          {
            $result['status'] = 'ERROR';
            $result['fileNames']['error'][] = $_FILES['file']['name'];
          }
          elseif ($status == 'FILE_EXISTS')
          {
            $result['status'] = 'ERROR';
            $result['fileNames']['exists'][] = $_FILES['file']['name'];
          }
        }
        else  //Multiple files, file[]
        {
          $fileCount = count($_FILES['file']['name']);
          
          for ($i=0; $i < $fileCount; $i++)
          {
            $status = $this->_addFile($_FILES['file']['tmp_name'][$i], $path, $_FILES['file']['name'][$i]);
            
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
      $fileMapper = new Application_Model_FileMapper();
      $files = $fileMapper->getForBrowserByIds($fileIds);

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
  
  public function removeFileAction()
  {
    $file = $this->_getValidFile();
    $fileMapper = new Application_Model_FileMapper();
    $fileMapper->getForBrowserById($file);
          
    if ($file->getExtraData('attachmentCount') > 0)
    {
      echo 'FILE_IS_ATTACHMENT';
    }
    else
    {
      $fileMapper->deleteById($file);
      @unlink($file->getFullPath(true));
      echo 'OK';
    }
    
    exit();
  }
  
  public function renameAction()
  { 
    $fileMapper = new Application_Model_FileMapper();
    
    $oldFile = new Application_Model_File();
    $oldFile->setId($this->getRequest()->getPost('id'));
    $oldFile = $fileMapper->getForBrowserById($oldFile);

    $newFile = new Application_Model_File();
    $newFile->setName($this->getRequest()->getPost('newName'));
    $newFile->setPath($oldFile->getPath());
    $newFile->setExtension($oldFile->getExtension());

    if ($oldFile === false || $newFile->getName() === null)
    {
      echo 'FILE_NOT_EXISTS';
    }
    elseif (file_exists($newFile->getFullPath(true)))
    {
      echo 'DESTINATION_FILE_ALREADY_EXISTS';
    }
    else
    {
      $fileMapper->rename($oldFile, $newFile);
      rename($oldFile->getFullPath(true), $newFile->getFullPath(true));
      echo 'OK';
    }
    
    exit();
  }
  
  private function _removeDirectory($path)
  {
    $result = 'OK';

    if (!empty($path))
    {
      $path = $this->_getFullPath($path);
      $fsPath = iconv('UTF-8', $this->_fileSystemEncoding, $path);
      
      if (file_exists($fsPath)) 
      {
        $fileMapper = new Application_Model_FileMapper();
        $files = $fileMapper->getByPath($path);

        if (count($files) > 0)
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

        $this->_removeDirectoryRecursive($fsPath);
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
  
  public function renameDirectoryAction()
  {
    $path = $this->_getClearPath();
    $newPath = $this->_getClearPath('newPath');

    if (!empty($path) && !empty($newPath))
    {
      $utf8Path = $this->_getFullPath($path);
      $utf8NewPath = $this->_getFullPath($newPath);
      $path = iconv('UTF-8', $this->_fileSystemEncoding, $utf8Path);
      $newPath = iconv('UTF-8', $this->_fileSystemEncoding, $utf8NewPath);

      if (!file_exists($path) || !is_dir($path))
      {
        echo 'DIRECTORY_NOT_EXISTS';
      }
      elseif (file_exists($newPath))
      {
        echo 'DESTINATION_DIRECTORY_ALREADY_EXISTS';
      }
      else
      {
        $fileMapper = new Application_Model_FileMapper();
        $fileMapper->renameDirectory($utf8Path, $utf8NewPath);
        rename($path, $newPath);
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
        @mkdir($path);
        echo 'OK';
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
    return $this->_clearPath($this->getRequest()->getPost($pathName, self::DIRECTORY_SEPARATOR));
  }
  
  private function _clearPath($path)
  {
    $path = preg_replace('/['.self::DIRECTORY_SEPARATOR.self::DIRECTORY_SEPARATOR.']+/u', self::DIRECTORY_SEPARATOR, $path);
    $path = str_replace(array('.'.self::DIRECTORY_SEPARATOR, '..'.self::DIRECTORY_SEPARATOR), '', $path);
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
  
  public function imagePreviewAction()
  {
    if ($this->getRequest()->get('id') > 0)
    {
      $file = $this->_getValidFileData();
      $info = @getimagesize($file->getFullPath(true));
      header('Content-type: '.$info['mime']);
      echo file_get_contents($file->getFullPath(true));
    }
    
    exit();
  }
}