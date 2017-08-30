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
class Project_Form_GroupForwardToExecuteTask extends Custom_Form_Abstract
{
  private $_ids = array();
  private $_projectId = null;
  private $_defaultReleaseId = null;
  private $_minDate;
  private $_maxDate = null;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('projectId', $options))
    {
       throw new Exception('Project id not defined in form.');
    }
    
    $this->_projectId = $options['projectId'];

    if (!array_key_exists('defaultReleaseId', $options) || $options['defaultReleaseId'] <= 0)
    {
       throw new Exception('Release id is not defined in form.');
    }
    
    $this->_defaultReleaseId = $options['defaultReleaseId'];

    if (array_key_exists('ids', $options['post']))
    {
      $names = array_keys($options['post']['ids']);
      $this->_ids = array();
      
      foreach ($names as $name)
      {
        if ($options['post']['ids'][$name])
        {
          $this->_ids[] = $this->_getIdFormName($name);
        }
      }
    }
    elseif (array_key_exists('taskIds', $options['post']))
    {
      $this->_ids = explode('_', $options['post']['taskIds']);
    }
    
    $nowDate = date('Y-m-d');

    if (array_key_exists('minDate', $options) && strtotime($this->_minDate) > strtotime($nowDate))
    {
       $this->_minDate = $options['minDate'];
    }
    else
    {
      $this->_minDate = $nowDate;
    }

    if (array_key_exists('maxDate', $options))
    {
      $this->_maxDate = $options['maxDate'];
    }
    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    $t = new Custom_Translate();    
    
    $this->addElement('text', 'releaseName', array(
      'required'  => false,
      'class'     => 'autocomplete',
      'maxlength' => 255,
      'value'     => $t->translate('defaultReleaseName', array(), 'general')
    ));
    
    $this->addElement('hidden', 'releaseId', array(
      'required'    => true,
      'value'       => $this->_defaultReleaseId,
      'validators'  => array(
        'Id',
        array('ReleaseExists', true)
      )
    ));

    $this->addElement('hidden', 'taskIds', array(
      'required'  => true,
      'ignore'    => false,
      'value'     => implode('_', $this->_ids)
    ));
      
    $this->addElement('text', 'environments', array(
      'required'   => false,
      'class'       => 'autocomplete',   
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Environments', false, array(
          'criteria' => array('project_id' => $this->_projectId))
      ))
    ));
    
    $this->addElement('text', 'versions', array(
      'required'   => false,
      'class'      => 'autocomplete', 
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Versions', false, array(
          'criteria' => array('project_id' => $this->_projectId))
      ))
    ));

    $this->addElement('select', 'priority', array( 
      'required'      => true,
      'value'         => Application_Model_TaskRunPriority::MAJOR,
      'multiOptions'  => array(
        Application_Model_TaskRunPriority::CRITICAL  => $t->translate('TASK_RUN_PRIORITY_CRITICAL', array(), 'type'),
        Application_Model_TaskRunPriority::MAJOR     => $t->translate('TASK_RUN_PRIORITY_MAJOR', array(), 'type'),
        Application_Model_TaskRunPriority::MINOR     => $t->translate('TASK_RUN_PRIORITY_MINOR', array(), 'type'),
        Application_Model_TaskRunPriority::TRIVIAL   => $t->translate('TASK_RUN_PRIORITY_TRIVIAL', array(), 'type')
      )
    ));
    
    $this->addElement('text', 'dueDate', array(
      'required'    => true,
      'maxlength'   => 16,
      'class'       => 'j_datetime',
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('Date', true, array('format' => 'YYYY-mm-dd'))
      )
    ));
    
    if ($this->_maxDate === null)
    {
      $this->getElement('dueDate')->addValidator('DateLater', false, array(
        'date'      => $this->_minDate.' 00:00:00',
        'inclusive' => true
      ));
    }
    else
    {
      $this->getElement('dueDate')->addValidator('DateBetween', false, array(
        'min'      => $this->_minDate.' 00:00:00',
        'max'      => $this->_maxDate.' 23:59:59',
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
    
    $this->addElement('textarea', 'comment', array(
      'maxlength'   => 1000,
      'required'    => false,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 1000, 'UTF-8')),
      ),
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'group_forward_to_execute_task',
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
  
  public function getValues($suppressArrayNotation = false)
  {
    $values = parent::getValues($suppressArrayNotation);
    $values['environments'] = strlen($values['environments']) ? explode(',', $values['environments']) : array();
    $values['versions'] = strlen($values['versions']) ? explode(',', $values['versions']) : array();
    $values['taskIds'] = explode('_', $values['taskIds']);
    return $values;
  }
  
  private function _getIdFormName($name)
  {
    $buf = explode('_', $name, 2);
    return $buf[1];
  }
  
  public function getMinDate()
  {
    return $this->_minDate;
  }
  
  public function getMaxDate()
  {
    return $this->_maxDate;
  }
  
  public function getDefaultReleaseId()
  {
    return $this->_defaultReleaseId;
  }
}