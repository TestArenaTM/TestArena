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
class Project_Form_CloneReleaseTasks extends Custom_Form_SubFormAbstract
{
  const OPTIONAL_REQUIRED_FIELD_1 = 'dueDate';
  const OPTIONAL_REQUIRED_FIELD_2 = 'environments';
  const OPTIONAL_REQUIRED_FIELD_3 = 'versions';
  
  public static $_optionalRequiredFields = array(
    array(
      'name'         => self::OPTIONAL_REQUIRED_FIELD_1,
      'defaultValue' => null
    ),
    array(
      'name'         => self::OPTIONAL_REQUIRED_FIELD_2,
      'defaultValue' => null
    ),
    array(
      'name'         => self::OPTIONAL_REQUIRED_FIELD_3,
      'defaultValue' => null
    )
  );
  
  private $_minDate;
  private $_maxDate = null;
  
  public function init()
  {
    parent::init();
    
    $tasks   = Zend_Registry::get('tasks');
    $release = Zend_Registry::get('release');
    
    $this->addElement('hidden', 'step', array(
      'belongsTo'   => 'step2[stepTwo]',
      'required'    => false,
      'value'       => '2',
      'validators' => array(
        'Digits',
        array('Between', false, array('min' => 1, 'max' => 2, 'inclusive' => true))
      )
    ));
    
    $this->addElement('text', 'environments', array(
      'belongsTo'  => 'step2[stepTwo]',
      'required'   => false,
      'class'      => 'autocomplete cloneOptionalField', 
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Environments', false, array(
          'criteria' => array('project_id' => $release->getProjectId()))
      ))
    ));
    
    $this->addElement('text', 'versions', array(
      'belongsTo'   => 'step2[stepTwo]',
      'required'   => false,
      'class'      => 'autocomplete cloneOptionalField', 
      'filters'    => array('StringTrim'),
      'validators' => array(
        array('Versions', false, array(
          'criteria' => array('project_id' => $release->getProjectId()))
      ))
    ));
    
    $this->addElement('text', 'dueDate', array(
      'belongsTo' => 'step2[stepTwo]',
      'required'    => true,
      'maxlength'   => 16,
      'class'       => 'j_datetime cloneOptionalField',
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('Date', true, array('format' => 'YYYY-MM-dd hh:ii'))
      )
    ));

    if (is_array($tasks) && count($tasks) > 0)
    {
      foreach($tasks as $task)
      {
        $this->addElement('hidden', 'task_' . $task->getId(), array(
          'belongsTo' => 'step2[stepTwo]',
          'required'  => false,
          'value'     => '',
          'attribs'   => array('disabled' => 'disabled')
        ));
      }
      
      if (Zend_Registry::isRegistered('formData'))
      {
        $formData = Zend_Registry::get('formData');
        
        if (array_key_exists('startDate', $formData))
        {
          $this->_minDate = $formData['startDate'];
        }

        if (array_key_exists('endDate', $formData))
        {
          $this->_maxDate = $formData['endDate'];
        }
        
        $isSelectedTasks = count(preg_filter('/^task_(.*)/', '$1', array_keys( $formData ))) > 0;
        
        //tmp solution
        if ((isset($formData['step']) || isset($formData['step2']))
            && (false === $isSelectedTasks && !isset($formData['tasks'])))
        {
          $this->disableFields();
        }
        elseif (isset($formData['postData'])
            && isset($formData['postData']['step']) 
            && !isset($formData['postData']['name']))
        {
          $isSelectedTasksFromPost = count(preg_filter('/^task_(.*)/', '$1', array_keys( $formData['postData'] ))) > 0;
          
          if (false === $isSelectedTasksFromPost) 
          {
            $this->disableFields();
          }
          
        }
        //tmp solution -end
      }
    }
    else
    {
      $this->disableFields();
    }
  }
  
  public function getEnvironments()
  {
    return explode(',', $this->getValue('environments'));
  }
  
  public function getVersions()
  {
    return explode(',', $this->getValue('versions'));
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
  
  public function getMinDate()
  {
    return $this->_minDate;
  }
  
  public function getMaxDate()
  {
    return $this->_maxDate;
  }
  
  public function disableFields()
  {
    foreach (self::$_optionalRequiredFields as $field)
    {
      $this->_disableField($field['name'], $field['defaultValue']);
    }
  }
  
  private function _disableField($fieldName, $defaultValue = null)
  {
    $this->getElement($fieldName)->setRequired(false)
                                 ->setAttrib('disabled', 'disabled')
                                 ->setValue($defaultValue);
  }
}