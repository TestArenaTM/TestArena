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
class Administration_Form_ImportProject extends Custom_Form_Abstract
{
  private $_destination = null;
  private $_fileName    = null;  
  
  public function __construct($options = null)
  {
    if (!array_key_exists('destinationDirectory', $options))
    {
      throw new Custom_500Exception('Not set destination directory for file.');
    }
    
    $this->_destination = $options['destinationDirectory'];

    if (!array_key_exists('fileName', $options))
    {
      throw new Custom_500Exception('Not set destination file name.');
    }
    
    $this->_fileName = $this->_destination.DIRECTORY_SEPARATOR.$options['fileName'];

    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    $fileSize = Zend_Registry::get('config')->max_file_size;
    
    $this->addElement('file', 'file', array(
      'required'  => true,
      'maxFileSize' => $fileSize,
      'destination' => $this->_destination,
      'filters'     => array(
        'Rename' => array('target' => $this->_fileName, 'overwrite' => true)
      ),
      'validators'  => array(
        'Count'     => array(false, 1),
        'Size'      => array('max' => $fileSize),
        'Extension' => array(false, 'zip')
      )
    ));
    
    $this->addElement('text', 'name', array(
      'required'    => true,
      'maxlength'   => 80,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('UniqueProjectName', true),
        array('StringLength', false, array(2, 80, 'UTF-8'))
      )
    ));
    
    $this->addElement('text', 'prefix', array(
      'required'    => true,
      'maxlength'   => 6,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'ProjectPrefix',
        array('UniqueProjectPrefix', true),
        array('StringLength', false, array(2, 6, 'UTF-8')),
      )
    ));
    
    $this->addElement('password', 'password', array(
      'required'    => true,
      'maxlength'   => 64,
      'validators'  => array(
        'Password',
        array('PasswordConfirmation', false, array('confirmFieldName' => 'confirmPassword')),
        array('StringLength', false, array(6, 64, 'UTF-8')),
      )
    ));
    
    $this->addElement('password', 'confirmPassword', array(
      'required'  => true,
      'maxlength' => 64
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'import_project',
      'timeout' => 600
    ));
  }
}