<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_Nl2Br implements Zend_Filter_Interface
{
  public function filter($value)
  {
      return nl2br($value);
  }
}

