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
class Project_Form_AddChecklist extends Project_Form_AddOtherTest
{
  private $_itemIndexes = array();
  
  public function init()
  {
    parent::init();
    
    $this->addElement('hidden', 'itemIds', array(
      'required'  => false,
      'isArray'   => true
    ));
    
    $this->addElement('hidden', 'itemNames', array(
      'required'  => true,
      'isArray'   => true
    ));
    
    $this->getElement('description')->setRequired(false);
    $this->getElement('csrf')->setAttrib('salt', 'add_checklist_test');
  }
  
  public function preparePostData(array $post)
  {
    $result = parent::preparePostData($post);
    $this->_itemIndexes = array();
    $itemIds = array();
    
    unset($result['itemIds']);
    unset($result['itemNames']);
    
    if (array_key_exists('itemIds', $post) && count($post['itemIds']))
    {
      foreach ($post['itemIds'] as $key => $id)
      {
        if (is_numeric($id))
        {
          $itemIds[$key] = $id;
        }
      }
    }
    
    if (array_key_exists('itemNames', $post) && count($post['itemNames']))
    {
      foreach ($post['itemNames'] as $key => $name)
      {
        $name = trim($name);
        $buf = explode('_', $key);
        
        if (strlen($name) > 0 && count($buf) == 2)
        {
          $index = $buf[1];
          $result['itemNames'][$key] = $name;

          $key = 'itemId_'.$index;
          $id = array_key_exists($key, $itemIds) ? $itemIds[$key] : 0;
          $result['itemIds'][$key] = $id;

          $this->_addItemElement($index, $id, $name);
          $this->_itemIndexes[] = $index;
        }
      }
      
      if (array_key_exists('itemIds', $result))
      {
        $result = array_merge($result, $result['itemIds']);
        $result = array_merge($result, $result['itemNames']);
      }
    }

    return $result;
  }
  
  public function prepareItemsFromDb(array $items)
  {
    $result = array();
    $this->_itemIndexes = array();
    
    if (count($items))
    {
      foreach ($items as $i => $item)
      {
        $this->_addItemElement($i, $item->getId(), $item->getName());
        $this->_itemIndexes[] = $i;

        $key = 'itemId_'.$i;
        $result[$key] = $this->getValue($key);
        $result['itemIds'][$key] = $result[$key];

        $key = 'itemName_'.$i;
        $result[$key] = $this->getValue($key);
        $result['itemNames'][$key] = $result[$key];
      }
    }

    return $result;
  }
  
  private function _addItemElement($index, $id, $name)
  {
    $key = 'itemId_'.$index;
    $this->addElement('hidden', $key, array( 
      'required'    => false,
      'value'       => $id,
      'id'          => $key,
      'belongsTo'   => 'itemIds',
      'validators'  => array()
    ));
    
    $key = 'itemName_'.$index;
    $this->addElement('text', $key, array( 
      'required'    => false,
      'value'       => $name,
      'id'          => $key,
      'belongsTo'   => 'itemNames',
      'maxlength'   => 255,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLength', false, array(1, 255, 'UTF-8'))
      )
    ));
  }
  
  public function getItemIndexes()
  {
    return $this->_itemIndexes;
  }
}