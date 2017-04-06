<?php
class Custom_Validate_ProjectElements extends Custom_Validate_DbCountAbstract
{
  const PROJECT_ELEMENTS_INVALID_IDS = 'projectElementsInvalidIds';
  const PROJECT_ELEMENT_NOT_EXISTS  = 'projectElementNotExists';
  
  protected $_messageTemplates = array
  (
    self::PROJECT_ELEMENTS_INVALID_IDS => 'Project elements ids can only be numbers.',
    self::PROJECT_ELEMENT_NOT_EXISTS => 'Project element not exists in database.'
  );
    
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'project_element';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $projectElements = explode(',', $value);
    
    foreach($projectElements as $element)
    {
      if (!is_numeric($element) || $element <= 0)
      {
        $this->_error(self::PROJECT_ELEMENTS_INVALID_IDS);
        return false;
      }
    }
    
    $result = $this->_countSelect($value, $projectElements);
    
    if (count($projectElements) != $result)
    {
      $this->_error(self::PROJECT_ELEMENT_NOT_EXISTS);
      return false;
    }
    
    return true;
  }
}