<?php
require_once('../application/const.php');
require_once(APPLICATION_PATH.'/models/AttachmentType.php');
require_once(APPLICATION_PATH.'/models/CommentSubjectType.php');
require_once(APPLICATION_PATH.'/models/HistorySubjectType.php');

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
  
  /**
   * TASK
   */
  
  // Get defect ids with DLETETED status
  $sql = 'SELECT id FROM defect WHERE status = :status';
  $q = $db->prepare($sql);
  $q->bindValue('status', 9, PDO::PARAM_INT);
  $q->execute(); 
  $defectIds = array();
  
  foreach ($q->fetchAll() as $row)
  {
    $defectIds[] = $row['id'];
  }
  
  $q->closeCursor();
  $q = null;
  
  if (count($defectIds) > 0)
  {
    $defectIds = implode(',', $defectIds);

    // Delete from attachment
    $sql = 'DELETE FROM attachment WHERE subject_id IN('.$defectIds.') AND type = :type';
    $q = $db->prepare($sql);
    $q->bindValue(':type', Application_Model_AttachmentType::DEFECT_ATTACHMENT, PDO::PARAM_INT);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from comment
    $sql = 'DELETE FROM comment WHERE subject_id IN('.$defectIds.') AND subject_type = :subject_type';
    $q = $db->prepare($sql);
    $q->bindValue(':subject_type', Application_Model_CommentSubjectType::DEFECT, PDO::PARAM_INT);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from history
    $sql = 'DELETE FROM history WHERE subject_id IN('.$defectIds.') AND subject_type = :subject_type';
    $q = $db->prepare($sql);
    $q->bindValue(':subject_type', Application_Model_HistorySubjectType::DEFECT, PDO::PARAM_INT);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from defect_environment
    $sql = 'DELETE FROM defect_environment WHERE defect_id IN('.$defectIds.')';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from defect_tag
    $sql = 'DELETE FROM defect_tag WHERE defect_id IN('.$defectIds.')';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from defect_version
    $sql = 'DELETE FROM defect_version WHERE defect_id IN('.$defectIds.')';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete defects
    $sql = 'DELETE FROM defect WHERE id IN('.$defectIds.')';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;  
  }
  
  $db->commit();
}
catch (Exception $e)
{
  $db->rollBack();
  die($e->getMessage());
}

echo 'OK';