<?php
require_once('../application/const.php');
require_once(APPLICATION_PATH.'/models/TestType.php');
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
  
  // Get task ids with DLETETED status
  $sql = 'SELECT id FROM task WHERE status = :status';
  $q = $db->prepare($sql);
  $q->bindValue('status', 5, PDO::PARAM_INT);
  $q->execute(); 
  $taskIds = array();
  
  foreach ($q->fetchAll() as $row)
  {
    $taskIds[] = $row['id'];
  }
  
  $q->closeCursor();
  $q = null;
  
  if (count($taskIds) > 0)
  {    
    $taskIds = implode(',', $taskIds);

    // Get task_test
    $sql = 'SELECT id FROM task_test WHERE task_id IN('.$taskIds.')';
    $q = $db->prepare($sql);
    $q->execute(); 
    $taskTestIds = array();

    foreach ($q->fetchAll() as $row)
    {
      $taskTestIds[] = $row['id'];
    }

    $q->closeCursor();
    $q = null;
  
    if (count($taskTestIds) > 0)
    {   
      $taskTestIds = implode(',', $taskTestIds);

      // Delete from comment
      $sql = 'DELETE FROM comment WHERE subject_id IN('.$taskTestIds.') AND subject_type = :subject_type';
      $q = $db->prepare($sql);
      $q->bindValue(':subject_type', Application_Model_CommentSubjectType::TASK_TEST, PDO::PARAM_INT);
      $q->execute();
      $q->closeCursor();
      $q = null;

      // Delete from history
      $sql = 'DELETE FROM history WHERE subject_id IN('.$taskTestIds.') AND subject_type = :subject_type';
      $q = $db->prepare($sql);
      $q->bindValue(':subject_type', Application_Model_HistorySubjectType::TASK_TEST, PDO::PARAM_INT);
      $q->execute();
      $q->closeCursor();
      $q = null;

      // Delete from task_checklist_item
      $sql = 'DELETE FROM task_checklist_item WHERE task_test_id IN('.$taskTestIds.')';
      $q = $db->prepare($sql);
      $q->execute();
      $q->closeCursor();
      $q = null;

      // Delete from task_test
      $sql = 'DELETE FROM task_test WHERE id IN('.$taskTestIds.')';
      $q = $db->prepare($sql);
      $q->execute();
      $q->closeCursor();
      $q = null;
    }

    // Delete from attachment
    $sql = 'DELETE FROM attachment WHERE subject_id IN('.$taskIds.') AND type = :type';
    $q = $db->prepare($sql);
    $q->bindValue(':type', Application_Model_AttachmentType::TASK_ATTACHMENT, PDO::PARAM_INT);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from comment
    $sql = 'DELETE FROM comment WHERE subject_id IN('.$taskIds.') AND subject_type = :subject_type';
    $q = $db->prepare($sql);
    $q->bindValue(':subject_type', Application_Model_CommentSubjectType::TASK, PDO::PARAM_INT);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from history
    $sql = 'DELETE FROM history WHERE subject_id IN('.$taskIds.') AND subject_type = :subject_type';
    $q = $db->prepare($sql);
    $q->bindValue(':subject_type', Application_Model_HistorySubjectType::TASK, PDO::PARAM_INT);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from task_defect
    $sql = 'DELETE FROM task_defect WHERE task_id IN('.$taskIds.') AND bug_tracker_id IS NULL';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from task_environment
    $sql = 'DELETE FROM task_environment WHERE task_id IN('.$taskIds.')';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from task_tag
    $sql = 'DELETE FROM task_tag WHERE task_id IN('.$taskIds.')';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete from task_version
    $sql = 'DELETE FROM task_version WHERE task_id IN('.$taskIds.')';
    $q = $db->prepare($sql);
    $q->execute();
    $q->closeCursor();
    $q = null;

    // Delete tasks
    $sql = 'DELETE FROM task WHERE id IN('.$taskIds.')';
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