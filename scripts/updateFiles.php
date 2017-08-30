<?php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'testx');
define('DB_USER', 'root');
define('DB_PASS', 'KOko$1410');

$db = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('SET NAMES utf8');

try
{
  $db->beginTransaction();

  // Teporary files
  $sql = 'UPDATE file SET project_id = NULL, subpath = :subpath WHERE project_id = :project_id';
  $q = $db->prepare($sql);
  $q->bindValue(':subpath', DIRECTORY_SEPARATOR, PDO::PARAM_STR);
  $q->bindValue(':project_id', 1, PDO::PARAM_INT);
  $q->execute();
  $q->closeCursor();
  $q = null;
  
  // Project files
  $sql = 'SELECT id, subpath FROM file WHERE project_id = 0';
  $q = $db->prepare($sql);
  $q->execute(); 
  $files = $q->fetchAll();
  $q->closeCursor();
  $q = null;
  
  $mainDirNameLength = strlen(_FILE_UPLOAD_DIR.DIRECTORY_SEPARATOR);
  $sql = 'UPDATE file SET project_id = :project_id, subpath = :subpath WHERE id = :id;';
  $q = $db->prepare($sql);
  
  foreach ($files as $file)
  {
    $length = strlen($file['subpath']) - $mainDirNameLength + 1;
    $subpath = substr($file['subpath'], $mainDirNameLength - 1, $length);
    $parts = explode(DIRECTORY_SEPARATOR, $subpath);
    $projectId = $parts[1];
    unset($parts[1]);
    $subpath = implode(DIRECTORY_SEPARATOR, $parts);

    $q->bindValue(':project_id', $projectId, PDO::PARAM_INT);
    $q->bindValue(':subpath', $subpath, PDO::PARAM_STR);
    $q->bindValue(':id', $file['id'], PDO::PARAM_INT);
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