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
abstract class Custom_Form_Abstract extends Zend_Form
{
  private static $_backUrlCount = 0;
  
  public function __construct($options = null)
  {
    $this->addElementPrefixPath('Custom', _LIBRARY_PATH.'/Custom/');
    //$this->addPrefixPath('Prefix_Form', 'Prefix/Form/');

    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('hidden', 'backUrl', array(
      'id'      => 'backUrl'.self::$_backUrlCount++,
      'ignore'  => true,
      'value'   => empty($_SERVER['HTTP_REFERER']) ? 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : $_SERVER['HTTP_REFERER']
    ));
  }
  
  public function getBackUrl()
  {
    return $this->getValue('backUrl');
  }
}