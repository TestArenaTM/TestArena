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
class User_Form_UserAvatar extends Custom_Form_Abstract
{
  private $_destination = null;
  private $_fileName    = null;
  protected $_imageSize = array(
    'minwidth'  => 50, 
    'minheight' => 50, 
    'maxwidth'  => 3000, 
    'maxheight' => 3000
  );
  
  public function __construct($options = null)
  {
    if (!array_key_exists('avatarDestinationDirectory', $options))
    {
      throw new Custom_500Exception('Not set destination directory for avatar.');
    }
    $this->_destination = $options['avatarDestinationDirectory'];

    if (!array_key_exists('avatarFileName', $options))
    {
      throw new Custom_500Exception('Not set destination file name for avatar.');
    }
    $this->_fileName = $this->_destination.DIRECTORY_SEPARATOR.$options['avatarFileName'];

    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    $fileSize = Zend_Registry::get('config')->max_avatar_file_size;
    
    $this->addElement('file', 'avatar', array(
      'required'  => true,
      'maxFileSize' => $fileSize,
      'destination' => $this->_destination,
      'filters'     => array(
        'Rename' => array('target' => $this->_fileName, 'overwrite' => true)
      ),
      'validators'  => array(
        'Count'     => array(false, 1),
        'Size'      => array('max' => $fileSize),
        'ImageSize' => $this->_imageSize,
        'Image'     => array(
          'maxFileSize'   => $fileSize,
          'supportedMime' => array('image/jpeg', 'image/png')
        ),
        'Extension' => array(false, 'png,jpg,jpeg,jpe,jif,jfif,jfi')
      )
    ));
        
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'user_avatar',
      'timeout' => 600
    ));
  }
  
  public function getImageMinWidth()
  {
    return $this->_imageSize['minwidth'];
  }
  
  public function getImageMinHeight()
  {
    return $this->_imageSize['minheight'];
  }
  
  public function getImageMaxWidth()
  {
    return $this->_imageSize['maxwidth'];
  }
  
  public function getImageMaxHeight()
  {
    return $this->_imageSize['maxheight'];
  }
}