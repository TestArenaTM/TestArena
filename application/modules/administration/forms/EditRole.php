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
class Administration_Form_EditRole extends Administration_Form_AddRole
{
  private $_roleId;
  
  public function __construct($options = null)
  {
    if (!array_key_exists('roleId', $options))
    {
      throw new Exception('Role id not defined in edit form');
    }

    $this->_roleId = $options['roleId'];
    
    parent::__construct($options);
  }
  
  public function init()
  {    
    parent::init();
    
    $this->getElement('name')
      ->removeValidator('Role_UniqueName')
      ->addValidator('Role_UniqueName', true, array('exclude' => $this->_roleId));
    
    $this->getElement('csrf')->setAttrib('salt', 'role_edit');
  }
  
  public function prepareJsonRoleData(array $roleData)
  {
    $result = array();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    if (count($roleData) > 0)
    {
      $result[] = array(
        'name'     => $htmlSpecialCharsFilter->filter($roleData['project$name']),
        'id'       => $roleData['project$id'],
        'readonly' => true
      );
    }
    
    return json_encode($result);
  }
  
  public function prepareRoleSettingCheckboxes(array $roleSettings)
  {
    $result = array();
    
    foreach($roleSettings as $setting)
    {
      if (is_array($setting) && count($setting) > 0)
      {
        $result['roleAction_'.$setting['role_action']] = true;
      }
      else
      {
        $result['roleAction_'.$setting] = true;
      }
    }  
    
    return $result;
  }
  
  public function prepareUsersDataForPopulate(array $users)
  {
    $result = null;
    
    if (count($users) > 0)
    {
      foreach ($users as $user)
      {
        if (null !== $result)
        {
          $result .= ',';
        }
        $result .= $user['id'];
      }  
    }
    
    return $result;
  }
}