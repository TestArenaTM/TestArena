<?php
class Custom_Validate_UniquePhaseName extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'phaseNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Phase name already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'phase';
    $options['field'] = 'name';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $result = $this->_uniqueSelect($value);

    if ($result[$this->_field] == $value)
    {
      $this->_error(self::ERROR_EXISTS);
      return false;
    }

    return true;
  }
}