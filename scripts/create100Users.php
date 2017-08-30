<?php
require_once('../application/const.php');
require_once(_LIBRARY_PATH.'/Utils/Text.php');
require_once(APPLICATION_PATH.'/models/User.php');
require_once(APPLICATION_PATH.'/models/UserStatus.php');

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'testx');
define('DB_USER', 'root');
define('DB_PASS', 'gebril');

$db = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('SET NAMES utf8');

try
{
  $db->beginTransaction();
  
  $sql = 'INSERT INTO user (email, password, salt, status, create_date, firstname, lastname, administrator, knowledge_keeper)';
  $sql .= ' VALUES (:email, :password, :salt, :status, :create_date, :firstname, :lastname, 1, 0)';
  $q = $db->prepare($sql);
  
  for ($i = 1; $i <= 120; $i++)
  {
    $user = new Application_Model_User();
    $user->setPassword('Pass'.$i.'23');
    $q->bindValue(':email', 'admin'.$i.'@testingcup.pl', PDO::PARAM_STR);
    $q->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
    $q->bindValue(':salt', $user->getSalt(), PDO::PARAM_STR);
    $q->bindValue(':status', Application_Model_UserStatus::ACTIVE, PDO::PARAM_STR);
    $q->bindValue(':create_date', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $q->bindValue(':firstname', 'Admin', PDO::PARAM_STR);
    $q->bindValue(':lastname', 'Admin', PDO::PARAM_STR);
    $q->execute();    
  }
  
  $q->closeCursor();
  $q = null;
  
  $db->commit();
}
catch (Exception $e)
{
  $db->rollBack();
  die($e->getMessage());
}

echo 'OK';