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
class User_Form_Login extends Custom_Form_Abstract
{
  private $_turnOnCaptcha = false;
  private $_session;
  
  public function __construct($options = null)
  {
    $this->_session = new Zend_Session_Namespace('LoginForm');

    if (array_key_exists('turnOnCaptcha', $options))
    {
      $this->_turnOnCaptcha = (bool)$options['turnOnCaptcha'];
    }
    
    parent::__construct($options);
  }
  
  public function init()
  {
    parent::init();
    
    $this->addElement('text', 'email', array(
      'required'   => true,
      'maxlength'   => 320,
      'filters'    => array('StringTrim'),
      'validators' => array(
        'EmailAddressSimpleMessage',
      )
    ));
    
    $this->addElement('password', 'password', array(
      'required'   => true,
      'maxlength'   => 64,
      'validators' => array(
        //'Password',
        //array('StringLength', false, array(6, 64, 'UTF-8'))
      )
    ));

    $this->addElement('checkbox', 'remember', array(
      'required' => false
    ));
       
    if ($this->_turnOnCaptcha)
    {
      $recaptchaConfigData = Zend_Registry::get('config')->recaptcha;
      $recaptchaOptions = array(
        'siteKey'   => $recaptchaConfigData->publicKey,
        'secretKey' => $recaptchaConfigData->privateKey,
      );

      $recaptcha = new Custom_Form_Element_Recaptcha('grecaptcharesponse', $recaptchaOptions);

      $this->addElement($recaptcha);
    }
    
    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'login',
      'timeout' => 600
    ));
  }
  
  public function isValid($data)
  {
    /*if ($this->_turnOnCaptcha && (!isset($this->_session->turnOnCaptcha) || $this->_session->turnOnCaptcha == false))
    {
      $this->getElement('grecaptcharesponse')->getCaptcha()->generate();
      $data['captcha'] = array(
        'id'    => $this->getElement('captcha')->getCaptcha()->getId(),
        'input' => $this->getElement('captcha')->getCaptcha()->getWord()
      );
    }*/

    $this->_session->turnOnCaptcha = $this->_turnOnCaptcha;

    return parent::isValid($data);
  }
}