<?php
/*
Copyright © 2014 TestArena 

This file is part of TestArena.

TestArena is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

The full text of the GPL is in the LICENSE file.
*/
class Application_Model_User extends Custom_Model_Standard_Avatar_Abstract implements Custom_Interface_ApplicationUser
{
  const GEN_SALT_LENGTH = 16;
  const SALT            = 'F754&^#$~ds78';
  
  protected $_map = array(
    'new_email'           => 'newEmail',
    'reset_password'      => 'resetPassword',
    'create_date'         => 'createDate',
    'last_login_date'     => 'lastLoginDate',
    'phone_number'        => 'phoneNumber',
    'default_project_id'  => 'defaultProjectId',
    'default_locale'      => 'defaultLocale'
  );
  
  private $_id                = null;
  private $_email             = null;
  private $_newEmail          = null;
  private $_password          = null;
  private $_salt              = null;
  private $_resetPassword     = null;
  private $_status            = null;
  private $_createDate        = null;
  private $_lastLoginDate     = null;
  private $_token             = null;
  private $_firstname         = null;
  private $_lastname          = null;
  private $_administrator     = null;
  private $_organization      = null;
  private $_department        = null;
  private $_phoneNumber       = null;
  private $_defaultProjectId  = null;
  private $_defaultLocale     = null;

  private $_roleSettings = array();
  private $_filters = array();
  
  static private $_availableLocales = array('pl_PL', 'en_US');

  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getEmail()
  {
    return $this->_email;
  }

  public function getNewEmail()
  {
    return $this->_newEmail;
  }

  public function getPassword()
  {
    return $this->_password;
  }

  public function getSalt()
  {
    return $this->_salt;
  }
  
  public function getResetPassword()
  {
    return $this->_resetPassword;
  }

  public function getStatus()
  {
    return $this->_status;
  }

  public function getStatusId()
  {
    return $this->_status->getId();
  }

  public function getCreateDate()
  {
    return $this->_createDate;
  }

  public function getLastLoginDate()
  {
    return $this->_lastLoginDate;
  }

  public function getToken()
  {
    return $this->_token;
  }
  
  public function getFirstname()
  {
    return $this->_firstname;
  }

  public function getLastname()
  {
    return $this->_lastname;
  }
  
  public function getFullname()
  {
    return $this->_firstname.' '.$this->_lastname;
  }

  public function getAdministrator()
  {
    return $this->_administrator;
  }
  
  public function isAdministrator()
  {
    return $this->_administrator === '1';
  }

  public function getOrganization()
  {
    return $this->_organization;
  }

  public function getDepartment()
  {
    return $this->_department;
  }

  public function getPhoneNumber()
  {
    return $this->_phoneNumber;
  }

  public function getRoleSettings()
  {
    return $this->_roleSettings;
  }
  
  public function getDefaultProjectId()
  {
    return $this->_defaultProjectId;
  }
  
  public function getDefaultLocale()
  {
    return $this->_defaultLocale;
  }  
  
  public function getFilters()
  {
    return $this->_filters;
  }  
  
  public function getFilter($groupId, $default = null)
  {
    return array_key_exists($groupId, $this->_filters) ? $this->_filters[$groupId] : $default;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }

  public function setEmail($email)
  {
    $this->_email = $email;
    return $this;
  }

  public function setNewEmail($email)
  {
    $this->_newEmail = $email;
    return $this;
  }

  public function setStatus($id)
  {
    $this->_status = new Application_Model_UserStatus($id);
    return $this;
  }

  public function setCreateDate($date)
  {
    $this->_createDate = $date;
    return $this;
  }

  public function setLastLoginDate($date)
  {
    $this->_lastLoginDate = $date;
    return $this;
  }

  public function setPassword($password)
  {
    $this->generateSalt();

    $this->_password = sha1(md5(self::addSaltToPassword((string)$password)).$this->getSalt());
    return $this;
  }

  public function setSalt($salt)
  {
    $this->_salt = $salt;
    return $this;
  }
  
  public function setResetPassword($resetPassword)
  {
    $this->_resetPassword = $resetPassword;
  }

  public function setToken($token)
  {
    $this->_token = $token;
    return $this;
  }
  
  public function setFirstname($firstname)
  {
    $this->_firstname = $firstname;
  }

  public function setLastname($lastname)
  {
    $this->_lastname = $lastname;
  }

  public function setAdministrator($administrator)
  {
    $this->_administrator = $administrator;
  }

  public function setOrganization($organization)
  {
    $this->_organization = $organization;
  }

  public function setDepartment($department)
  {
    $this->_department = $department;
  }

  public function setPhoneNumber($phoneNumber)
  {
    $this->_phoneNumber = $phoneNumber;
  }
  
  public function setRoleSettings(array $roleSettings)
  {
    foreach($roleSettings as $setting)
    {
      $this->setRoleSetting($setting);
    }
    return $this;
  }
  
  public function setRoleSetting(array $setting)
  {
    if (array_key_exists('role_action_id', $setting) && is_numeric($setting['role_action_id']))
    {
      $this->_roleSettings[] = new Application_Model_RoleSetting(array('roleAction' => $setting['role_action_id']));
    }
    
    return $this;
  }
  
  public function setDefaultProjectId($defaultProjectId = 0)
  {
    $this->_defaultProjectId = $defaultProjectId;
  }
  
  public function setDefaultLocale($defaultLocale)
  {
    if (self::isAvailableLocale($defaultLocale))
    {
      $this->_defaultLocale = $defaultLocale;
    }
  }
  // </editor-fold>
  
  public function generateToken()
  {
    $this->setToken(Utils_Text::generateToken());
    return $this->getToken();
  }

  public function generateSalt()
  {
    $this->setSalt(substr(Utils_Text::generateToken(), 0, self::GEN_SALT_LENGTH));
    return $this->getSalt();
  }

  public static function addSaltToPassword($password)
  {
    return $password . self::SALT;
  }
  
  public static function clearIdentity()
  {
    $cookieDomain = Zend_Registry::get('config')->cookie_domain;
    
    Zend_Auth::getInstance()->clearIdentity();
    setcookie('FrameProfile', '', 0, '/', $cookieDomain, false, true);
    setcookie('RememberMe', '', 0, '/', $cookieDomain, false, true);
  }
  
  public function checkRolePermission($roleActionObjectId, $roleActionId, $project)
  {
    if (null === $project)
    {
      throw new Custom_AccessDeniedException();
    }
    
    $rolePermissions = $this->getPermissionSettings($project->getId());
    
    if (count($rolePermissions) > 0)
    {
      foreach ($rolePermissions as $permission)
      {
        if ($permission->checkAccess($roleActionObjectId, $roleActionId))
        {
          return true;
        }
      }
    }
    
    return false;
  }

  public function setBaseAvatarDirectoryName()
  {
    $this->_baseAvatarDirectoryName = 'user';
  }
  
  public function addFilter(Application_Model_Filter $filter)
  {
    $this->_filters[$filter->getGroupId()] = $filter;
    return $this;
  }
  
  static public function isAvailableLocale($language)
  {
    return in_array($language, self::$_availableLocales);
  }
}