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
class Administration_Form_AddRole extends Custom_Form_Abstract
{
  private $_roleActions = array();
  
  public function __construct($options = null)
  {
    if (!array_key_exists('roleActions', $options) && count($options['roleActions']) < 1)
    {
      throw new Exception('Role action setting checkbox field not set.');
    }
    
    $this->_roleActions = $options['roleActions'];
    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    $t = new Custom_Translate();
    
    $this->addElement('text', 'name', array(
      'required'   => true,
      'maxlength'  => 30,
      'filters'    => array('StringTrim'),
      'validators' => array(
        'Name',
        'Role_UniqueName',
        array('StringLength', false, array(3, 30, 'UTF-8')),
      )
    ));
    
    $this->addElement('text', 'projects', array(
      'required'   => true,
      'class'      => 'autocomplete',  
      'filters'    => array('StringTrim'),
      'validators' => array(
        'Role_Projects'
      )
    ));
    
    $this->addElement('select', 'type', array( 
      'required'   => false,
      'multiOptions' => array(
        Application_Model_Role::TYPE_1 => $t->translate('TEMPLATE_CUSTOM', array(), 'role'),
        Application_Model_Role::TYPE_2 => $t->translate('TEMPLATE_LEADER', array(), 'role'),
        Application_Model_Role::TYPE_3 => $t->translate('TEMPLATE_TESTER', array(), 'role'),
        Application_Model_Role::TYPE_4 => $t->translate('TEMPLATE_PROGRAMMER', array(), 'role'),
        Application_Model_Role::TYPE_5 => $t->translate('TEMPLATE_GUEST', array(), 'role')
      )
    ));
    
    $this->addElement('text', 'users', array(
      'required'    => false,
      'filters'     => array('ArrayTrim'),
      'validators'  => array(
        'Users'
      )
    )); 
    
    $this->addElement('hidden', 'roleSettings', array('isArray' => true));
    
    $this->addElement('hidden', 'roleSettingsEmptyValidation', array(
      'validators' => array(
        'Role_Settings'
      ),
      'value' => '1'
    ));

    foreach($this->_roleActions as $actionId => $actionName)
    {
      $this->addElement('checkbox', 'roleAction_'.$actionId, array(
        'filters'         => array('StringTrim'),
        'required'        => false,
        'checkedValue'    => $actionId,
        'uncheckedValue'  => 0,
        'belongsTo'       => 'roleSettings'
      ));
    }
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'role_add',
      'timeout' => 600
    ));
  }
  
  public function prepareJsonData(array $data)
  {
    $result = array();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    if (count($data) > 0)
    {
      foreach($data as $single)
      {
        $result[] = array(
          'name' => $htmlSpecialCharsFilter->filter($single['name']),
          'id'   => $single['id']
        );
      }
    }
    
    return json_encode($result);
  }
}