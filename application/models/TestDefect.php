<?php

class Application_Model_TestDefect
{
  private $_id = null;
  private $_defect = null;
  private $_taskTest = null;
  private $_bugTrackerId = null;


  // <editor-fold defaultstate="collapsed" desc="Getters">
  /**
   * @return int
   */
  public function getId()
  {
    return $this->_id;
  }

  /**
   * @return Application_Model_TaskTest
   */
  public function getTaskTest()
  {
    return $this->_taskTest;
  }

  /**
   * @return Application_Model_Defect
   */
  public function getDefect()
  {
    return $this->_defect;
  }

  /**
   * @return integer
   */
  public function getBugTrackerId()
  {
    return $this->_bugTrackerId;
  }

  // </editor-fold>


  // <editor-fold defaultstate="collapsed" desc="Setters">
  /**
   * @param int $id
   */
  public function setId($id)
  {
    $this->_id = $id;
  }

  /**
   * @param integer $bugTrackerId
   */
  public function setBugTrackerId($bugTrackerId)
  {
    $this->_bugTrackerId = $bugTrackerId;
  }

  public function setDefectObject(Application_Model_Defect $defect)
  {
    $this->_defect = $defect;
  }

  /**
   * @param Application_Model_TaskTest $taskTest
   */
  public function setTaskTestObject(Application_Model_TaskTest $taskTest)
  {
    $this->_taskTest = $taskTest;
  }


  public function setDefectJira($propertyName, $propertyValue)
  {
    if (null === $this->_defect)
    {
      $this->_defect = new Application_Model_DefectJira(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getDefect()->setProperty($propertyName, $propertyValue);
    }

    return $this;
  }

  public function setDefectMantis($propertyName, $propertyValue)
  {
    if (null === $this->_defect)
    {
      $this->_defect = new Application_Model_DefectMantis(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getDefect()->setProperty($propertyName, $propertyValue);
    }

    return $this;
  }

  // </editor-fold>


}