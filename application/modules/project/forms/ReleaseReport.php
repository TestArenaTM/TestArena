<?php
/*
Copyright © 2017 TestArena

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
class Project_Form_ReleaseReport extends Custom_Form_Abstract
{
  public function init()
  {
    parent::init();
    $t = new Custom_Translate();   
    
    $this->addElement('select', 'type', array( 
      'required'      => true,
      'validators'    => array('FormSelectWrongValue'),
      'multiOptions'  => array(
        0 => $t->translate('[Wybierz]', array(), 'general'),
        Application_Model_Release::PDF_REPORT => $t->translate('PDF_REPORT', array(), 'type'),
        Application_Model_Release::CSV_REPORT => $t->translate('CSV_REPORT', array(), 'type')
      )
    ));
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'release_report',
      'timeout' => 600
    ));
  }
}