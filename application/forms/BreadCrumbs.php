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
class Application_Form_BreadCrumbs extends Zend_Form_SubForm
{
  public function addBreadCrumbs($breadCrumbs, $subFormName)
  {
    foreach ($breadCrumbs as $k => $singleBreadCrumbData)
    {
      $breadCrumb = new Zend_Form_Element_Submit(
        $singleBreadCrumbData['form'],
        array(
          'belongsTo'  => $subFormName.'[breadCrumbs]',
          'label'      => _($singleBreadCrumbData['label']),
          'required'   => false,
          'ignore'     => true,
          'order'      => $k,
          'decorators' => array(
            'viewHelper',
            'Errors'
          )
        )
      );

      $attribs = $this->_setAttributesBasedOnStatus($singleBreadCrumbData, array('class' => 'breadCrumbButton'));
      $breadCrumb->setAttribs($attribs);

      $this->addElement($breadCrumb);
    }

    return $this;
  }

  public function addBreadCrumbsToMenu($breadCrumbs)
  {
    $menulink = '<dt id="breadCrumbs-label">&#160;</dt><dd id="breadCrumbs-element"><fieldset id="fieldset-breadCrumbs"><dl>';
    
    foreach ($breadCrumbs as $k => $singleBreadCrumbData)
    {
      $menulink .= '<input type="submit" name="step' . $k . '[breadCrumbs][step' . $k . ']" id="step' . $k . '-breadCrumbs-step' . $k . '" value="' . $v['label'] . '" class="';
      $attribs = $this->_setAttributesBasedOnStatus($singleBreadCrumbData, array('class' => 'button'));
      
      $menulink .= $attribs['class'];
      $menulink .= '" />';
    }

    echo $menulink;
    return $this;
  }
  
  private function _setAttributesBasedOnStatus($singleBreadCrumbData, $attribs)
  {
    if (!$singleBreadCrumbData['enabled'])
    {
      $attribs['disabled'] = 'disabled';
      $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "disabled";
    }
    else
    {
      $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "enabled";
    }

    if ($singleBreadCrumbData['active'])
    {
      $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "active";
    }

    if ($singleBreadCrumbData['valid'])
    {
      $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "valid";
    }
    else
    {
      $attribs['class'] = ($attribs['class'] != "" ? $attribs['class'] . " " : "") . "invalid";
    }
    
    return $attribs;
  }
}