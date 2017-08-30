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
class Project_Form_AddOtherTest extends Custom_Form_Abstract
{  
  protected $_projectId;
  
  public function __construct($options = null)
  {    
    if (!array_key_exists('projectId', $options))
    {
       throw new Exception('Project id not defined in form');
    }
    
    $this->_projectId = $options['projectId'];
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'name', array(
      'required'    => true,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('StringLength', false, array(3, 255, 'UTF-8')),
        array('UniqueTestName', true, array(
          'criteria' => array('project_id' => $this->_projectId)
        ))
      )
    ));
    
    $this->addElement('textarea', 'description', array(
      'maxlength'   => 5000,
      'required'    => true,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 5000, 'UTF-8')),
      ),
    ));
    
    $this->addElement('hidden', 'attachmentIds', array(
      'required'  => false,
      'isArray'   => true,
      'value'     => 1
    ));
    
    $this->addElement('hidden', 'attachmentNames', array(
      'required'  => false,
      'isArray'   => true,
      'value'     => 1
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'add_other_test',
      'timeout' => 600
    ));
  }
  
  public function getValues($suppressArrayNotation = false)
  {
    $values = parent::getValues($suppressArrayNotation);
    
    if (!isset($values['attachmentIds']))
    {
      $values['attachmentIds'] = array();
    }

    return $values;
  }
  
  public function preparePostData(array $post)
  {
    $result = $post;
    
    if (array_key_exists('attachmentIds', $post) && count($post['attachmentIds'])
      && array_key_exists('attachmentNames', $post) && count($post['attachmentNames']))
    {
      $result = array_merge($result, $post['attachmentIds']);
      $result = array_merge($result, $post['attachmentNames']);
      
      foreach ($post['attachmentIds'] as $key => $id)
      {
        $this->addElement('hidden', $key, array(
          'required'  => false,
          'belongsTo' => 'attachmentIds',
          'value'     => $id,
          'class'     => 'j_attachmentId'
        ));
      }
      
      foreach ($post['attachmentNames'] as $key => $name)
      {
        $this->addElement('hidden', $key, array(
          'required'  => false,
          'belongsTo' => 'attachmentNames',
          'value'     => $name,
          'class'     => 'j_attachmentName'
        ));
      }
    }
    
    return $result;
  }
  
  public function getAttachmentIds($keys = false)
  {
    $values = $this->getValue('attachmentIds');

    if (is_array($values))
    {
      return $keys ? array_keys($this->getValue('attachmentIds')) : $this->getValue('attachmentIds');
    }
    
    return array();
  }
  
  public function getAttachmentNames($keys = false)
  {
    $values = $this->getValue('attachmentNames');

    if (is_array($values))
    {
      return $keys ? array_keys($this->getValue('attachmentNames')) : $this->getValue('attachmentNames');
    }
    
    return array();
  }
  
  public function prepareAttachmentsFromDb(array $files)
  {
    $result = array();
    
    if (count($files))
    {
      foreach ($files as $i => $file)
      {
        $key = 'attachmentId_'.$i;
        $this->addElement('hidden', $key, array(
          'required'  => false,
          'belongsTo' => 'attachmentIds',
          'value'     => $file->getId(),
          'class'     => 'j_attachmentId'
        ));
        $result[$key] = $this->getValue($key);
        $result['attachmentIds'][$key] = $result[$key];
        
        $key = 'attachmentName_'.$i;
        $this->addElement('hidden', $key, array(
          'required'  => false,
          'belongsTo' => 'attachmentNames',
          'value'     => $file->getFullName(),
          'class'     => 'j_attachmentName'
        ));
        $result[$key] = $this->getValue($key);
        $result['attachmentNames'][$key] = $result[$key];
      }
    }
    
    return $result;
  }
}