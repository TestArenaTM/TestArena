<?php
class Custom_Validate_Role_Projects extends Custom_Validate_DbCountAbstract
{
  const ROLE_PROJECTS_INVALID_IDS = 'roleProjectsInvalidIds';
  const ROLE_PROJECT_NOT_EXISTS   = 'roleProjectNotExists';
  
  protected $_messageTemplates = array
  (
    self::ROLE_PROJECTS_INVALID_IDS => 'Role project ids can only be numbers.',
    self::ROLE_PROJECT_NOT_EXISTS => 'Role project not exists in database.'
  );
    
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'project';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $projects = explode(',', $value);
    
    foreach($projects as $project)
    {
      if (!is_numeric($project) || $project <= 0)
      {
        $this->_error(self::ROLE_PROJECTS_INVALID_IDS);
        return false;
      }
    }
    
    $result = $this->_countSelect($value, $projects);
    
    if (count($projects) != $result)
    {
      $this->_error(self::ROLE_PROJECT_NOT_EXISTS);
      return false;
    }
    
    return true;
  }
}