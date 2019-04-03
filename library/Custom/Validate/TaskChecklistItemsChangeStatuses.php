<?php
class Custom_Validate_TaskChecklistItemsChangeStatuses
{  
  private $taskTest;

  public function setTaskTest(Application_Model_TaskTest $taskTest)
  {
    $this->taskTest = $taskTest;
  }
  
  public function isValid($status, $ids = array())
  {
    foreach ($this->taskTest->getChecklistItems() as $item) {
      if ($item->getStatus()->getId() != $status && in_array($item->getId(), $ids)) {
        return true;
      }
    }
    return false;
  }
}