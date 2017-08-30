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
  public function downloadAction()
  {
    $file = $this->_getValidFile();
    $session = new Zend_Session_Namespace('FileDownload');
    $layout = 'default';
    
    if (isset($session->layout))
    {
      $layout = $session->layout;
    }
    else if ($file->getProject() !== null)
    {
      $layout = 'project';
    }
    
    $this->_helper->layout->setLayout($layout);
    $this->_setTranslateTitle();
    $this->view->file = $file;
    $this->view->backUrl = $this->_getBackUrl('file_dwonload', null, true);
  }
  
  public function downloadProcessAction()
  {
    $file = $this->_getValidFile();
    $download = new Utils_Download($file->getFullPath(true), $file->getFullName());
    
    if ($download->save() === false)
    {
      throw new Custom_404Exception();
    }
    
    exit();
  }
  
  public function thumbnailAction()
  {
    $file = $this->_getValidFile();
    
    try
    {
      $image = new Utils_Image($file->getFullPath(true));
      $image->fit(50, 50);
      $image->show();
    }
    catch (Utils_Image_Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      echo file_get_contents(_FRONT_PUBLIC_DIR.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'default_file_thumbs.png');
      header('Content-type: image/png');
    }
    
    exit();
  }
  
  public function previewImageAction()
  {
    $file = $this->_getValidFile();
    
    try
    {
      $image = new Utils_Image($file->getFullPath(true));
      $image->show();
    }
    catch (Utils_Image_Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
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
    
    $file = new Application_Model_File($idValidator->getFilteredValues());
    $fileMapper = new Application_Model_FileMapper();
    
    if ($fileMapper->getById($file) === false || 
      ($file->getProject()->getId() > 0 && !array_key_exists($file->getProject()->getId(), $this->_projects)))
    {
      throw new Custom_404Exception();
    } 

    return $file;
  }
}