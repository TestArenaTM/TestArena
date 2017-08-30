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
class Project_Form_AddTask extends Custom_Form_Abstract
{
  private $_projectId = null;
  private $_minDate;
  private $_maxDate = null;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('projectId', $options))
    {
       throw new Exception('Project id not defined in form.');
    }
    
    $this->_projectId = $options['projectId'];   

    if (isset($options['minDate']) && isset($options['maxDate']))
    {
      $this->_minDate = $options['minDate'];
      $this->_maxDate = $options['maxDate'];
    }
    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    $t = new Custom_Translate();   
    
    $this->addElement('text', 'title', array(
      'required'    => true,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('StringLength', false, array(3, 255, 'UTF-8')),
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
    
    $this->addElement('text', 'releaseName', array(
      'required'  => false,
      'class'     => 'autocomplete',
      'maxlength' => 255,
      'value'     => ''
    ));
    
    $this->addElement('hidden', 'releaseId', array(
      'required'    => false,
      'value'       => '',
      'validators'  => array(
        'Id',
        array('ReleaseExists', true)
      )
    ));
    
    $this->addElement('text', 'environments', array(
      'required'   => true,
      'class'      => 'autocomplete', 
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Environments', false, array(
          'criteria' => array('project_id' => $this->_projectId))
      ))
    ));
    
    $this->addElement('text', 'versions', array(
      'required'   => true,
      'class'      => 'autocomplete', 
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Versions', false, array(
          'criteria' => array('project_id' => $this->_projectId))
      ))
    ));
    
    $this->addElement('text', 'tags', array(
      'required'   => false,
      'class'      => 'autocomplete', 
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Tags', false, array(
          'criteria' => array('project_id' => $this->_projectId))
      ))
    ));

    $this->addElement('select', 'priority', array( 
      'required'      => true,
      'value'         => Application_Model_TaskPriority::MAJOR,
      'multiOptions'  => array(
        Application_Model_TaskPriority::CRITICAL  => $t->translate('TASK_PRIORITY_CRITICAL', array(), 'type'),
        Application_Model_TaskPriority::MAJOR     => $t->translate('TASK_PRIORITY_MAJOR', array(), 'type'),
        Application_Model_TaskPriority::MINOR     => $t->translate('TASK_PRIORITY_MINOR', array(), 'type'),
        Application_Model_TaskPriority::TRIVIAL   => $t->translate('TASK_PRIORITY_TRIVIAL', array(), 'type')
      )
    ));
    
    $this->addElement('text', 'dueDate', array(
      'required'    => true,
      'maxlength'   => 16,
      'class'       => 'j_datetime',
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('Date', true, array('format' => 'YYYY-MM-dd hh:ii'))
      )
    ));
    
    if ($this->_maxDate !== null)
    {
      $this->getElement('dueDate')->addValidator('DateBetween', false, array(
        'min'      => $this->_minDate,
        'max'      => $this->_maxDate,
        'inclusive' => true
      ));
    }
    else
    {
      $this->getElement('dueDate')->addValidator('DateLater', false, array(
        'date'      => $this->_minDate,
        'inclusive' => true
      ));
    }
    
    $this->addElement('text', 'assigneeName', array(
      'required'    => false,
      'class'       => 'autocomplete', 
      'maxlength'   => 255
    ));
    
    $this->addElement('hidden', 'assigneeId', array(
      'required'    => true,
      'validators'  => array(
        'Id',
        array('UserIdExists', true)
      )
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
      'salt'    => 'add_task',
      'timeout' => 600
    ));
  }
  
  public function prePopulateEnvironments(array $environments)
  {
    $result = array();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    if (count($environments) > 0)
    {
      foreach($environments as $environment)
      {
        $result[] = array(
          'name' => $htmlSpecialCharsFilter->filter($environment['name']),
          'id'   => $environment['id']
        );
      }
    }
    
    return json_encode($result);
  }
  
  public function prePopulateVersions(array $versions)
  {
    $result = array();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    if (count($versions) > 0)
    {
      foreach($versions as $version)
      {
        $result[] = array(
          'name' => $htmlSpecialCharsFilter->filter($version['name']),
          'id'   => $version['id']
        );
      }
    }
    
    return json_encode($result);
  }
  
  public function prePopulateTags(array $tags)
  {
    $result = array();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    if (count($tags) > 0)
    {
      foreach($tags as $tag)
      {
        $result[] = array(
          'name' => $htmlSpecialCharsFilter->filter($tag['name']),
          'id'   => $tag['id']
        );
      }
    }
    
    return json_encode($result);
  }
  
  public function getValues($suppressArrayNotation = false)
  {
    $values = parent::getValues($suppressArrayNotation);
    $values['environments'] = strlen($values['environments']) ? explode(',', $values['environments']) : array();
    $values['versions'] = strlen($values['versions']) ? explode(',', $values['versions']) : array();
    $values['tags'] = strlen($values['tags']) ? explode(',', $values['tags']) : array();
    
    if (!isset($values['attachmentIds']))
    {
      $values['attachmentIds'] = array();
    }

    return $values;
  }
  
  public function getMinDate()
  {
    return $this->_minDate;
  }
  
  public function getMaxDate()
  {
    return $this->_maxDate;
  }
  
  public function prepareAttachments(array $post)
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
  
  public function getEnvironments()
  {
    return explode(',', $this->getValue('environments'));
  }
  
  public function getVersions()
  {
    return explode(',', $this->getValue('versions'));
  }
  
  public function getTags()
  {
    return explode(',', $this->getValue('tags'));
  }
}