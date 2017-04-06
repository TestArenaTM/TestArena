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
class Administration_Form_UserFilter extends Custom_Form_AbstractFilter
{
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
    
    $this->addElement('select', 'status', array( 
      'required'    => false,
      'multiOptions' => array(
        0                                       => $t->translate('[Wszystkie]', array(), 'general'),
        Application_Model_UserStatus::INACTIVE  => $t->translate('USER_INACTIVE', array(), 'status'),
        Application_Model_UserStatus::ACTIVE    => $t->translate('USER_ACTIVE', array(), 'status')
      )
    ));
    
    $this->addElement('select', 'administrator', array( 
      'required'    => false,
      'multiOptions' => array(
        0 => $t->translate('[Wszyscy]', array(), 'general'),
        1  => $t->translate('Tak', array(), 'general'),
        2  => $t->translate('Nie', array(), 'general')
      )
    ));
  }
}