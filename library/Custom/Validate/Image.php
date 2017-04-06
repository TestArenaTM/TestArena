<?php

require_once 'Zend/Validate/Abstract.php';

class Custom_Validate_Image extends Zend_Validate_Abstract
{
  const IS_CORRUPTED          = 'imageIsCorrupted';
  const UNSUPPORTED_TYPE      = 'imageUnsupportedType';
  const FILE_SIZE_TOO_BIG     = 'fileSizeTooBig';

  protected $_messageTemplates = array (
    self::IS_CORRUPTED          => 'Image file is corrupted.',
    self::UNSUPPORTED_TYPE      => 'Unsupported image type.',
    self::FILE_SIZE_TOO_BIG     => 'File is too big.',
  );
  
  private $_maxFileSize = null;
  private $_supportedMime = array();
  
  public function __construct($options = array())
  {
    if (array_key_exists('maxFileSize', $options) && is_numeric($options['maxFileSize']))
    {
      $this->_maxFileSize = $options['maxFileSize'];
    }
    
    if (array_key_exists('supportedMime', $options) && is_array($options['maxFileSize']))
    {
      $this->_supportedMime = $options['supportedMime'];
    }
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $info = getimagesize($value);
    
    if (false === $info)
    {
      $this->_error(self::IS_CORRUPTED);
      return false;
    }
    elseif (count($this->_supportedMime))
    {
      if (!in_array($info['mime'], $this->_supportedMime))
      {
        $this->_error(self::UNSUPPORTED_TYPE);
        return false;
      }
    }

    if ($this->_maxFileSize !== null  && filesize($value) > $this->_maxFileSize)
    {
      $this->_error(self::FILE_SIZE_TOO_BIG);
      return false;
    }
    
    return true;
  }
}