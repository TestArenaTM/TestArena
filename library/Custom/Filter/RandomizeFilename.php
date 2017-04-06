<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_RandomizeFilename implements Zend_Filter_Interface
{
  public function filter($value)
  {
    $oldFilename = $value;
    $info = pathinfo($value);
    $newFilename = $info['dirname'].DIRECTORY_SEPARATOR.$info['filename'].'_'.Utils_Text::generateToken().'.'.$info['extension'];

    if (!rename($oldFilename, $newFilename))
    {
        return $oldFilename;
    }
    
    return $newFilename;
  }
}

