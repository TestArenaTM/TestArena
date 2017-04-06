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
class Project_Form_CloseTask extends Custom_Form_Abstract
{
  private $_projectId = null;
  private $_resolutions = array();
  
  public function __construct($options = null)
  {
    if (!array_key_exists('resolutions', $options) || !is_array($options['resolutions']))
    {
      throw new Exception('Resolutions not defined in form.');
    }
    
    $this->_resolutions = $options['resolutions'];
    
    if (!array_key_exists('projectId', $options))
    {
      throw new Exception('Project id not defined in form.');
    }
    
    $this->_projectId = $options['projectId'];
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();    
    $t = new Custom_Translate();  
    
    $this->addElement('text', 'environments', array(
      'required'   => true,
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

    $this->addElement('select', 'resolutionId', array( 
      'required'      => true,
      'validators'    => array('FormSelectWrongValue'),
      'multiOptions'  => array(0 => $t->translate('[Wybierz]', array(), 'general'))
    ));

    $this->getElement('resolutionId')->addMultiOptions($this->_resolutions);
    
    $this->addElement('textarea', 'comment', array(
      'maxlength'   => 160,
      'required'    => false,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 160, 'UTF-8')),
      ),
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'end_task',
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
  
  public function getEnvironments()
  {
    return explode(',', $this->getValue('environments'));
  }
  
  public function getVersions()
  {
    return explode(',', $this->getValue('versions'));
  }
  
  public function getValues($suppressArrayNotation = false)
  {
    $values = parent::getValues($suppressArrayNotation);
    $values['environments'] = strlen($values['environments']) ? explode(',', $values['environments']) : array();
    $values['versions'] = strlen($values['versions']) ? explode(',', $values['versions']) : array();
    return $values;
  }
}