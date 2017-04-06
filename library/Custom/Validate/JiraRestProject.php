<?php
class Custom_Validate_JiraRestProject extends Zend_Validate_Abstract
{
  const INVALID_PROJECT_KEY                     = 'jiraInvalidProjectKey';
  const URL_OR_AUTHORIZATION_DATA_IS_INCORRECT  = 'jiraUrlOrAuthorizationDataIsIncorrect';
  
  protected $_messageTemplates = array
  (
    self::URL_OR_AUTHORIZATION_DATA_IS_INCORRECT  => 'JIRA url or authorization data is incorrect.',
    self::INVALID_PROJECT_KEY                     => 'Invalid JIRA project key.',
  );
  
  private $_urlFieldName = '';
  private $_projectKeyFieldName = '';
  private $_userNameFieldName = '';
  private $_passwordFieldName = '';
  
  public function __construct($options = array())
  {
    if (array_key_exists('urlFieldName', $options))
    {
      $this->_urlFieldName = $options['urlFieldName'];
    }
    
    if (array_key_exists('projectKeyFieldName', $options))
    {
      $this->_projectKeyFieldName = $options['projectKeyFieldName'];
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
    $projectKey = null;
    $userName = null;
    $password = null;
    
    if (is_array($context))
    {
      if (array_key_exists($this->_urlFieldName, $context))
      {
        $urlFilter = new Custom_Filter_Url();
        $url = $urlFilter->filter($context[$this->_urlFieldName]);
      }
      
      if (array_key_exists($this->_projectKeyFieldName, $context))
      {
        $projectKey = $context[$this->_projectKeyFieldName];
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

    if (empty($projectKey) && empty($userName) && empty($password))
    {
      return false;
    }
    
    try
    {
      if (!Utils_Api_Jira::projectExists($projectKey, $url, $userName, $password))
      {
        $this->_error(self::INVALID_PROJECT_KEY);
        return false;
      }
    }
    catch (Exception $e)
    {
      $this->_error(self::URL_OR_AUTHORIZATION_DATA_IS_INCORRECT);
      return false;
    }

    return true;
  }
}