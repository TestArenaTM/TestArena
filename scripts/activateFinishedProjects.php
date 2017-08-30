<?php
require_once('../application/const.php');
require_once(_LIBRARY_PATH.'/Utils/Text.php');
//require_once(APPLICATION_PATH.'/models/Project.php');
//require_once(APPLICATION_PATH.'/models/ProjectStatus.php');

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'local_testx');
define('DB_USER', 'local_testx');
define('DB_PASS', '12qwas');

$db = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('SET NAMES utf8');

try
{
  $db->beginTransaction();
  
  $sql = 'UPDATE project SET status = :status_active WHERE status = :status_finished';
  $q = $db->prepare($sql);
  $q->bindValue(':status_active', 1, PDO::PARAM_INT);
  $q->bindValue(':status_finished', 3, PDO::PARAM_INT);
  $q->execute();
  
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