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
class Project_Form_TaskFilter extends Custom_Form_AbstractFilter
{  
  protected $_userList;
  protected $_releaseList;
  protected $_environmentList;
  protected $_versionList;
  protected $_project;
  
  public function __construct($options = null)
  {    
    if (!array_key_exists('userList', $options))
    {
      throw new Exception('User list is not defined in form.');
    }

    if (!array_key_exists('releaseList', $options))
    {
      throw new Exception('Release list is not defined in form.');
    }

    if (!array_key_exists('environmentList', $options))
    {
      throw new Exception('Environment list is not defined in form.');
    }

    if (!array_key_exists('versionList', $options))
    {
      throw new Exception('Version list is not defined in form.');
    }

    if (!array_key_exists('project', $options))
    {
      throw new Exception('Project is not defined in form.');
    }

    $this->_userList = $options['userList'];
    $this->_releaseList = $options['releaseList'];
    $this->_environmentList = $options['environmentList'];
    $this->_versionList = $options['versionList'];
    $this->_project = $options['project'];
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    $this->setMethod('get');
    $this->setName('filterForm');
    $t = new Custom_Translate();
    
    $this->addElement('text', 'search', array(
      'required'    => false,
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLength', false, array(1, 255, 'UTF-8'))
      )
    ));
    
    $statusOptions = array(
      0                                         => $t->translate('[Wszystkie]', array(), 'general'),
      Application_Model_TaskStatus::OPEN        => $t->translate('TASK_OPEN', array(), 'status'),
      Application_Model_TaskStatus::IN_PROGRESS => $t->translate('TASK_IN_PROGRESS', array(), 'status'),
      Application_Model_TaskStatus::CLOSED      => $t->translate('TASK_CLOSED', array(), 'status'),
      Application_Model_TaskStatus::REOPEN      => $t->translate('TASK_REOPEN', array(), 'status')
    );
    
    $this->addElement('select', 'status', array( 
      'required'      => false,
      'multiOptions'  => $statusOptions
    ));
    
    $this->addElement('select', 'priority', array( 
      'required'    => false,
      'multiOptions' => array(
        0                                        => $t->translate('[Wszystkie]', array(), 'general'),
        Application_Model_TaskPriority::CRITICAL => $t->translate('TASK_PRIORITY_CRITICAL', array(), 'type'),
        Application_Model_TaskPriority::MAJOR    => $t->translate('TASK_PRIORITY_MAJOR', array(), 'type'),
        Application_Model_TaskPriority::MINOR    => $t->translate('TASK_PRIORITY_MINOR', array(), 'type'),
        Application_Model_TaskPriority::TRIVIAL  => $t->translate('TASK_PRIORITY_TRIVIAL', array(), 'type'),
      )
    ));
    
    $this->addElement('checkbox', 'onlyProject', array(
      'required'       => false,
      'uncheckedValue' => ''
    ));
    
    $this->addElement('select', 'release', array( 
      'required'    => false,
      'multiOptions' => array(
        0  => $t->translate('[Wszystkie]', array(), 'general'),
        -1 => $t->translate('[Bez wydania]', array(), 'general')
      )
    ));
    
    $this->getElement('release')->addMultiOptions($this->_releaseList);
    
    $this->addElement('select', 'assigner', array( 
      'required'    => false,
      'multiOptions' => array(
        0 => $t->translate('[Wszyscy]', array(), 'general')
      )
    ));
    
    $this->getElement('assigner')->addMultiOptions($this->_userList);
    
    $this->addElement('select', 'assignee', array( 
      'required'    => false,
      'multiOptions' => array(
        0 => $t->translate('[Wszyscy]', array(), 'general')
      )
    ));
    
    $this->getElement('assignee')->addMultiOptions($this->_userList);
    
    $this->addElement('select', 'environment', array( 
      'required'    => false,
      'multiOptions' => array(
        0 => $t->translate('[Wszystkie]', array(), 'general')
      )
    ));
    
    $this->getElement('environment')->addMultiOptions($this->_environmentList);
    
    $this->addElement('select', 'version', array( 
      'required'    => false,
      'multiOptions' => array(
        0 => $t->translate('[Wszystkie]', array(), 'general')
      )
    ));
    
    $this->getElement('version')->addMultiOptions($this->_versionList);
    
    $this->addElement('text', 'tags', array(
      'required'   => false,
      'class'      => 'autocomplete', 
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Tags', false, array(
          'criteria' => array('project_id' => $this->_projectId))
      ))
    ));
    
    $this->addElement('checkbox', 'exceededDueDate', array(
      'required'       => false,
      'uncheckedValue' => ''
    ));
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
    $values['tags'] = strlen($values['tags']) ? explode(',', $values['tags']) : array();
    return $values;
  }
  
  public function getTags()
  {
    return explode(',', $this->getValue('tags'));
  }
  
  public function getDefaultValues()
  {
    return json_encode(array(
      'resultCountPerPage' => 10,
      'search' => '',
      'status' => 0,
      'priority' => 0,
      'release' => 0,
      'assigner' => 0,
      'assignee' => 0,
      'environment' => 0,
      'version' => 0,
      'tags' => array('type' => 'tokenInput', 'values' => ''),
      'exceededDueDate' => false
    ));
  }
}