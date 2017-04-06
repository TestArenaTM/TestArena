<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_Tags implements Zend_Filter_Interface
{
  public function filter($value)
  {
    $buf = explode(',', $value);
    $value = '';

    foreach ($buf as $i => $tag)
    {
      $tag = Utils_Text::unicodeTrim($tag);
      
      if (!empty($tag))
      {
        if ($i > 0)
        {
          $value .= ', ';
        }
        $value .= $tag;
      }
    }

    return $value;
  }
}

