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
class Project_Form_AddPhase extends Custom_Form_Abstract
{
  private $_minDate;
  private $_maxDate;
  protected $_releaseId;
  protected $_releaseName = '';
  
  public function __construct($options = null)
  {
    if (array_key_exists('minDate', $options))
    {
      $this->_minDate = $options['minDate'];
    }

    if (array_key_exists('maxDate', $options))
    {
      $this->_maxDate = $options['maxDate'];
    }
    
    if (array_key_exists('releaseId', $options))
    {
      $this->_releaseId = $options['releaseId'];
    }
    
    if (array_key_exists('releaseName', $options))
    {
      $this->_releaseName = $options['releaseName'];
    }
    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'releaseName', array(
      'required'  => false,
      'class'     => 'autocomplete', 
      'maxlength' => 255,
      'value'     => $this->_releaseName
    ));
    
    $this->addElement('hidden', 'releaseId', array(
      'required'    => true,
      'value'       => $this->_releaseId,
      'validators'  => array(
        'Id',
        array('ReleaseExists', true)
      )
    ));
    
    $this->addElement('text', 'name', array(
      'required'    => true,
      'maxlength'   => 64,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'Name',
        array('StringLength', false, array(2, 64, 'UTF-8'))
      )
    ));
    
    if ($this->_releaseId !== null)
    {
      $this->getElement('name')->addValidator('UniquePhaseName', true, array(
        'criteria' => array('release_id' => $this->_releaseId)
      ));
    }
    
    $this->addElement('text', 'startDate', array(
      'required'    => true,
      'maxlength'   => 11,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('Date', true, array('format' => 'YYYY-mm-dd')),
        array('DateEarlierToField', false, array('fieldName' => 'endDate', 'inclusive' => true))
      )
    ));
    
    $this->addElement('text', 'endDate', array(
      'required'    => true,
      'maxlength'   => 11,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        array('Date', true, array('format' => 'YYYY-mm-dd')),
        array('DateLaterToField', false, array('fieldName' => 'startDate', 'inclusive' => true))
      )
    ));
    
    if ($this->_minDate !== null && $this->_maxDate !== null)
    {
      $this->getElement('startDate')->addValidator('DateBetween', false, array(
        'min'       => $this->_minDate,
        'max'       => $this->_maxDate,
        'inclusive' => true
      ));
      
      $this->getElement('endDate')->addValidator('DateBetween', false, array(
        'min'       => $this->_minDate,
        'max'       => $this->_maxDate,
        'inclusive' => true
      ));
    }
    
    $this->addElement('textarea', 'description', array(
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
      'salt'    => 'add_phase',
      'timeout' => 600
    ));
  }
  
  public function getMinDate()
  {
    return $this->_minDate;
  }
  
  public function getMaxDate()
  {
    return $this->_maxDate;
  }
}