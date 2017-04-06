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
class User_Model_Validator_Activate extends Custom_InputData_Abstract
{
  public function initValidators()
  {
    $this->addValidators('email', array(
      new Zend_Validate_EmailAddress()
    ), true);

    $this->addValidators('token', array(
      new Custom_Validate_Token()
    ), true);
  }
  
  public function initFilters()
  {
    $this->addFilters('email', array(
      new Custom_Filter_HtmlSpecialChars()
    ));

    $this->addFilters('token', array(
      new Custom_Filter_HtmlSpecialChars()
    ));
  }
  
  public function isValid(array $values)
  {
    $newValues = array();
    
    if (array_key_exists('uid', $values))
    {
      $newValues['email'] = base64_decode($values['uid']);
    }
    
    if (array_key_exists('code', $values))
    {
      $newValues['token'] = $values['code'];
    }

    return parent::isValid($newValues);
  }
}