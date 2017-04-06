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
class Project_Form_CloneRelease extends Project_Form_AddRelease
{
  private $_id;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('id', $options))
    {
       throw new Exception('Release id not defined in form');
    }
    
    $this->_id = $options['id'];    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'phaseName', array(
      'required'    => false,
      'maxlength'   => 64,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('StringLength', false, array(2, 64, 'UTF-8'))
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
    
    $this->getElement('csrf')->setAttrib('salt', 'clone_release');
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
}