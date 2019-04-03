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
class Project_Form_AddRelease extends Custom_Form_Abstract
{
  protected $_projectId;
  private $_minDate;
  
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
      'maxlength'   => 64,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('UniqueReleaseName', true, array(
          'criteria' => array('project_id' => $this->_projectId)
        )),
        array('StringLength', false, array(2, 64, 'UTF-8'))
      )
    ));
    
    $this->addElement('text', 'startDate', array(
      'required'    => true,
      'maxlength'   => 11,
      'autocomplete' => 'off',
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('Date', true, array('format' => 'YYYY-mm-dd')),
        array('DateEarlierToField', false, array('fieldName' => 'endDate', 'inclusive' => true))
      )
    ));
    
    $this->addElement('text', 'endDate', array(
      'required'    => true,
      'maxlength'   => 11,
      'autocomplete' => 'off',
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('Date', true, array('format' => 'YYYY-mm-dd')),
        array('DateLaterToField', false, array('fieldName' => 'startDate', 'inclusive' => true))
      )
    ));
    
    $this->addElement('textarea', 'description', array(
      'maxlength'   => 160,
      'required'    => false,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 160, 'UTF-8'))
      ),
    ));
    
    $this->addElement('checkbox', 'active', array(
      'required'       => false,
      'checkedValue'   => '1',
      'uncheckedValue' => '0'
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'add_release',
      'timeout' => 600
    ));
  }
  
  public function getMinDate()
  {
    return $this->_minDate;
  }
}