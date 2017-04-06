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
class Application_Form_Project extends Custom_Form_Abstract
{
  private $_projects = array();
  
  public function __construct($options = null)
  {
    $this->setName('project');
    $this->setMethod('post');
    $t = new Custom_Translate();
    $this->_projects = array(0 => $t->translate('[Wszystkie projekty]', array(), 'general'));
    
    if (array_key_exists('projects', $options))
    {
      foreach ($options['projects'] as $id => $name)
      {
        $this->_projects[$id] = $name;
      }
    }
    
    parent::__construct($options);
  }
  
  public function init()
  {
    $this->addElement('select', 'activeProject', array( 
      'required'      => true,
      'class'         => 'chosen-select',
      'multioptions'  => $this->_projects
    ));
  }
}