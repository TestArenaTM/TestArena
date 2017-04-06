<?php
class Custom_Validate_MantisSoapProject extends Zend_Validate_Abstract
{
  const URL_IS_INCORRECT    = 'mantisUrlIsIncorrect';
  const PROJECT_NOT_EXISTS  = 'mantisProjectNotExists';
  const ACCESS_DENIED       = 'mantisAccessDenied';
  
  protected $_messageTemplates = array
  (
    self::URL_IS_INCORRECT    => 'Mantis url is incorrect.',
    self::PROJECT_NOT_EXISTS  => 'Mantis project not exists.',
    self::ACCESS_DENIED       => 'The user does not have access to the project.',
  );
  
  private $_urlFieldName = '';
  private $_projectNameFieldName = '';
  private $_userNameFieldName = '';
  private $_passwordFieldName = '';
  
  public function __construct($options = array())
  {
    if (array_key_exists('urlFieldName', $options))
    {
      $this->_urlFieldName = $options['urlFieldName'];
    }
    
    if (array_key_exists('projectNameFieldName', $options))
    {
      $this->_projectNameFieldName = $options['projectNameFieldName'];
    }
    
    if (array_key_exists('userNameFieldName', $options))
    {
      $this->_userNameFieldName = $options['userNameFieldName'];
    }
    
    if (array_key_exists('passwordFieldName', $options))
    {
      $this->_passwordFieldName = $options['passwordFieldName'];
    }
  }
  
  public function isValid($value, $context = null)
  {
    $this->_setValue($value);
    $url = '';
    $projectName = null;
    $userName = null;
    $password = null;
    
    if (is_array($context))
    {
      if (array_key_exists($this->_urlFieldName, $context))
      {
        $urlFilter = new Custom_Filter_Url();
        $url = $urlFilter->filter($context[$this->_urlFieldName]);
      }
      
      if (array_key_exists($this->_projectNameFieldName, $context))
      {
        $projectName = $context[$this->_projectNameFieldName];
      }
      
      if (array_key_exists($this->_userNameFieldName, $context))
      {
        $userName = $context[$this->_userNameFieldName];
      }
      
      if (array_key_exists($this->_passwordFieldName, $context))
      {
        $password = $context[$this->_passwordFieldName];
      }
    }
    
    try
    {
      $projectId = Utils_Api_Mantis::getProjectIdByName($url, $projectName, $userName, $password);
      
      if ($projectId <= 0)
      {
        $this->_error(self::PROJECT_NOT_EXISTS);
        return false;
      }
      
      if (!Utils_Api_Mantis::checkUserHaveAccessToProject($url, $projectName, $userName, $password))
      {
        $this->_error(self::ACCESS_DENIED);
        return false;
      }
    }
    catch (Exception $e)
    {
      $this->_error(self::URL_IS_INCORRECT);
      return false;
    }

    return true;
  }
}