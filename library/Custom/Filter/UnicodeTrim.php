<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_UnicodeTrim implements Zend_Filter_Interface
{
  public function filter($value)
  {
    return Utils_Text::unicodeTrim($value);
  }
}

