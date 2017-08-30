<?php
require_once('../application/const.php');
require_once(APPLICATION_PATH.'/models/BugTrackerStatus.php');
require_once(APPLICATION_PATH.'/models/BugTrackerType.php');

if (APPLICATION_ENV == 'production')
{
  define('DB_HOST', 'sql.testarena.nazwa.pl');
  define('DB_PORT', 3307);
  define('DB_NAME', 'testarena_18');
  define('DB_USER', 'testarena_18');
  define('DB_PASS', 'DaT@20!$P@s$');
}
elseif (APPLICATION_ENV == 'pawel_piaskowy')
{
  define('DB_HOST', 'localhost');
  define('DB_PORT', 3306);
  define('DB_NAME', 'testx');
  define('DB_USER', 'root');
  define('DB_PASS', 'gebril');
}
else
{
  define('DB_HOST', 'sql.testarena.nazwa.pl');
  define('DB_PORT', 3307);
  define('DB_NAME', 'testarena_18');
  define('DB_USER', 'testarena_18');
  define('DB_PASS', 'DaT@20!$P@s$');
}

$db = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('SET NAMES utf8');

try
{
  $db->beginTransaction();
  
  $sql = 'SELECT id FROM project';
  $q = $db->prepare($sql);
  $q->execute(); 
  $projects = $q->fetchAll();
  $q->closeCursor();
  $q = null;
  
  $sql = 'INSERT INTO project_bug_tracker (project_id, bug_tracker_id, name, bug_tracker_type, bug_tracker_status)';
  $sql .= ' VALUES (:project_id, NULL, "INTERNAL", :bug_tracker_type, :bug_tracker_status)';
  $q = $db->prepare($sql);
  $q->bindValue(':bug_tracker_type', Application_Model_BugTrackerType::INTERNAL, PDO::PARAM_INT);
  $q->bindValue(':bug_tracker_status', Application_Model_BugTrackerStatus::ACTIVE, PDO::PARAM_INT);
  
  foreach ($projects as $project)
  {
    $q->bindValue(':project_id', $project['id'], PDO::PARAM_INT);
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