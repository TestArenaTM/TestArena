<?php

abstract class Custom_Plugin_CsrfProtection
{
  public static function getElement()
  {
    return new Zend_Form_Element_Hash(self::getFieldName(), array(
      'salt' => 'unique',
      'timeout' => 600,
      'decorators' => array(
        'viewHelper', array(
          'htmlTag', array(
            'tag' => 'dd', 'class' => 'noDisplay'
          )
        ),
        'Errors'
      )));
  }

    
  public static function addCsrfProtection($form)
  {
    return $form->addElement(self::getElement());
  }

  public static function getFieldName()
  {
    return Zend_Registry::get('config')->form->csrf->element_name;
  }
}