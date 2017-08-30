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
class Application_Form_Contact extends Custom_Form_Abstract
{
  private $_email = '';
  
  public function __construct($options = null)
  {
    if (array_key_exists('email', $options))
    {
      $this->_email = $options['email'];
    }
    
    parent::__construct($options);
  }

  public function init()
  {
    $this->setMethod('post');
    
    $this->addElement('text', 'name', array(
      'maxlength'   => 256,
      'required'    => true,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLength', true, array(1, 256, 'UTF-8')),
      ),
    ));

    $this->addElement('text', 'email', array(
      'maxlength'   => 256,
      'required'    => true,
      'filters'     => array('StringTrim'),
      'value'       => $this->_email,
      'validators'  => array(
        'EmailAddressSimpleMessage',
        array('StringLength', false, array(1, 256, 'UTF-8')),
      ),
    ));

    $this->addElement('text', 'subject', array(
      'maxlength'   => 256,
      'required'    => true,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLength', false, array(1, 256, 'UTF-8')),
      ),
    ));
        
    $this->addElement('textarea', 'message', array(
      'maxlength'   => 4000,
      'required'    => true,
      'filters'     => array('StringTrim'),
      'validators'  => array(
        'SimpleText',
        array('StringLengthOneCharacterLineBreaks', false, array(1, 4000, 'UTF-8')),
      ),
    ));
    
    $recaptchaConfigData = Zend_Registry::get('config')->recaptcha;
    $recaptchaOptions = array(
      'siteKey'   => $recaptchaConfigData->publicKey,
      'secretKey' => $recaptchaConfigData->privateKey,
    );
    
    $recaptcha = new Custom_Form_Element_Recaptcha('grecaptcharesponse', $recaptchaOptions);
    
    $this->addElement($recaptcha);
        
    /*$this->addElement('captcha', 'captcha', array(
      'required'  => true,
      'captcha'   => array(
        'captcha' => 'Image',
        'wordLen' => 6,
        'timeout' => 300,
        'font'    => _FRONT_PUBLIC_DIR . '/css/fonts/Oswald.otf',  
        'imgDir'  => _FRONT_PUBLIC_DIR . '/captcha/',  
        'imgUrl'  => Zend_Registry::get('config')->baseUrl . '/captcha/'
      ),
      'decorators' => array('Captcha') 
    ));*/

    $this->addElement('hash', 'csrf', array(
      'ignore'  => true,
      'salt'    => 'contact',
      'timeout' => 600
    ));
  }
}