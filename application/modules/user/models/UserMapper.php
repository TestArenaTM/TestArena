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
class User_Model_UserMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'User_Model_UserDbTable';

  public function getByEmail(Application_Model_User $user)
  {
    $row = $this->_getDbTable()->getByEmail($user->getEmail());
    
    if (null === $row)
    {
      return false;
    }
    
    return $user->setDbProperties($row->toArray());
  }
  
  public function getIdByTokenEmail(Application_Model_User $user)
  {
    $id = $this->_getDbTable()->getIdByTokenEmail($user->getToken(), $user->getEmail());
    
    if (empty($id))
    {
      return false;
    }
    
    $user->setId($id);
    return true;
  }
  
  public function getForPopulateByIds(array $ids, $returnRowData = false)
  {
    $result = $this->_getDbTable()->getForPopulateByIds($ids);
    
    if ($returnRowData)
    {
      return $result->toArray();
    }
    
    return $result;
  }
  
  public function setLastLoginDate(Application_Model_User $user)
  {
    $user->setLastLoginDate(date('Y-m-d H:i:s'));

    $data = array(
      'last_login_date' => $user->getLastLoginDate()
    );

    if (null === $user->getEmail())
    {
      return false;
    }
    
    return $this->_getDbTable()->update($data, array('email = ?' => $user->getEmail()));
  }
  
  public function setNewEmail(Application_Model_User $user)
  {
    if (null === $user->getId())
    {
      return false;
    }
 
    $data = array(
      'token'     => $user->generateToken(),
      'new_email' => $user->getNewEmail()
    );
    
    return $this->_getDbTable()->update($data, array('id = ?' => $user->getId()));
  }
   
  public function getNewEmailByTokenEmail(Application_Model_User $user)
  {
    $newEmail= $this->_getDbTable()->getNewEmailByTokenEmail($user->getToken(), $user->getEmail());
    
    if (empty($newEmail))
    {
      return false;
    }
    
    $user->setNewEmail($newEmail);
    return true;
  }
 
  public function changeEmail(Application_Model_User $user)
  {
    if (null === $user->getEmail())
    {
      return false;
    }
    
    $data = array(
      'email'     => $user->getNewEmail(),
      'new_email' => null,
      'token'     => null
    );    

    return $this->_getDbTable()->update($data, array('email = ?' => $user->getEmail())) == 1;
  }
  
  public function changePassword(Application_Model_User $user)
  {
    if (null === $user->getId())
    {
      return false;
    }
    
    $data = array(
      'password'        => $user->getPassword(),
      'salt'            => $user->getSalt(),
      'token'           => null,
      'reset_password'  => 0
    );
 
    try
    {
      $this->_getDbTable()->update($data, array('id = ?' => $user->getId()));
      
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }

  public function getForRecoverPassword(Application_Model_User $user)
  {
    if (null === $user->getEmail())
    {
      return false;
    }
    
    $row = $this->_getDbTable()->getForRecoverPassword($user->getEmail());
    
    if (null === $row)
    {
      return false;
    }
    
    $user->setDbProperties($row->toArray());
    return true;
  }
  
  public function setNewTokenById(Application_Model_User $user)
  {
    if (null === $user->getId())
    {
      return false;
    }
 
    $data = array(
      'token' => $user->generateToken()
    );
    
    return $this->_getDbTable()->update($data, array('id = ?' => $user->getId())) == 1;
  }
  
  public function changeAvatar(Application_Model_User $user)
  {
    if ($user->avatarExists())
    {
      $this->deleteAvatar($user);
    }
    
    $tempFileName = $user->getAvatarDirectory(Application_Model_User::AVATAR_DIRCTORY_TEMP).DIRECTORY_SEPARATOR.$user->getId();

    try
    {
      $avatar = new Utils_Image($tempFileName);
	    $avatar->setMaxFileSize(Zend_Registry::get('config')->avatar->max_image_file_size);
      //$avatar->fitIn(150, 150)->save(false, $user->getAvatarDirectory(Application_Model_User::AVATAR_DIRECTORY));
      $avatar->fitAndCrop(150, 150)->save(false, $user->getAvatarDirectory(Application_Model_User::AVATAR_DIRECTORY));
      $avatar->load($tempFileName)->fitAndCrop(50, 50)->save(false, $user->getAvatarDirectory(Application_Model_User::AVATAR_DIRECTORY_MINI));
    }
    catch (Utils_Image_Exception $e)
    {
      return $e->getCode() == Utils_Image_Exception::FILE_NOT_EXISTS ? false : $e->getMessage();
    }
    catch (Exception $e)
    {
      return false;
    }
    
    @unlink($tempFileName);
    
    return true;
  }
  
  public function deleteAvatar(Application_Model_User $user)
  {
    if ($user->avatarExists())
    {
      @unlink($user->getAvatar());
    }
    
    if ($user->avatarExists(true))
    {
      @unlink($user->getAvatar(true));
    }
    
    return true;
  }
  
  public function edit(Application_Model_User $user)
  {
    $data = array(
      'firstname'     => $user->getFirstname(),
      'lastname'      => $user->getLastname(),
      'organization'  => $user->getOrganization(),
      'department'    => $user->getDepartment(),
      'phone_number'  => $user->getPhoneNumber()
    );
    
    try
    {
      $this->_getDbTable()->update($data, array('id = ?' => $user->getId()));
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
    
    return true;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getAllAjax($request)->toArray();
  }
  
  public function getAllAjaxForMessage(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getAllAjaxForMessage($request)->toArray();
  }
  
  //for notifications
  
  public function getAllForNotification(Custom_Interface_Notification $notification)
  {
    $rows = $this->_getDbTable()->getAllForNotification($notification);
    
    if (empty($rows))
    {
      return false;
    }
    
    $result = array();
    
    foreach ($rows as $row)
    {
      $product = new Application_Model_User();
      $product->setDbProperties($row->toArray());
      
      $result[$row->id] = $product;
    }

    return $result;
  }
  
  public function getAllBLockedForNotification(Custom_Interface_Notification $notification)
  {
    $rows = $this->_getDbTable()->getAllBLockedForNotification($notification);
    
    if (empty($rows))
    {
      return false;
    }
    
    $result = array();
    
    foreach ($rows as $row)
    {
      $product = new Application_Model_User();
      $product->setDbProperties($row->toArray());
      
      $result[$row->id] = $product;
    }

    return $result;
  }
}