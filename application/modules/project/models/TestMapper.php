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
class Project_Model_TestMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_TestDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setCurrentPageNumber($request->getParam('page'));
    $resultCountPerPage = (int)$request->getParam('resultCountPerPage');
    $paginator->setItemCountPerPage($resultCountPerPage > 0 ? $resultCountPerPage : 10);
    
    $list = array();
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      $test = new Application_Model_Test($row);
      $list[$test->getId()] = $test;
    }
    
    return array($list, $paginator);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request, Application_Model_Project $project)
  {
    return $this->_getDbTable()->getAllAjax($request, $project->getId())->toArray();
  }
  
  public function getForViewInTask(Application_Model_Test $test, Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getForViewInTask($test->getId(), $project->getId());
    
    if (null === $row)
    {
      return false;
    }

    return $test->setDbProperties($row->toArray());
  }
  
  public function getForView(Application_Model_Test $test)
  {
    $row = $this->_getDbTable()->getForView($test->getId(), $test->getProjectId());
    
    if (null === $row)
    {
      return false;
    }

    return $test->setDbProperties($row->toArray());
  }
  
  public function getOtherTestForView(Application_Model_Test $test)
  {
    $row = $this->_getDbTable()->getOtherTestForView($test->getId(), $test->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $test->setDbProperties($row->toArray());
  }
  
  public function getOtherTestForEdit(Application_Model_Test $test)
  {
    $row = $this->_getDbTable()->getOtherTestForEdit($test->getId(), $test->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    $rowData = $row->toArray();
    $test->setDbProperties($rowData);
    return $test->map($rowData);
  }
  
  public function getTestCaseForView(Application_Model_TestCase $testCase)
  {
    $row = $this->_getDbTable()->getTestCaseForView($testCase->getId(), $testCase->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $testCase->setDbProperties($row->toArray()); 
  }
  
  public function getTestCaseForEdit(Application_Model_TestCase $testCase)
  {
    $row = $this->_getDbTable()->getTestCaseForEdit($testCase->getId(), $testCase->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    $rowData = $row->toArray();    
    $testCase->setDbProperties($rowData);    
    return $testCase->map($rowData);
  }
  
  public function getExploratoryTestForView(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $row = $this->_getDbTable()->getExploratoryTestForView($exploratoryTest->getId(), $exploratoryTest->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $exploratoryTest->setDbProperties($row->toArray()); 
  }
  
  public function getExploratoryTestForEdit(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $row = $this->_getDbTable()->getExploratoryTestForEdit($exploratoryTest->getId(), $exploratoryTest->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    $rowData = $row->toArray();    
    $exploratoryTest->setDbProperties($rowData);    
    return $exploratoryTest->map($rowData);
  }
  
  public function getPreviousNextByTest(Custom_Interface_Test $test)
  {
    $rows = $this->_getDbTable()->getPreviousNextByTest($test->getId(), $test->getProjectId());
    
    foreach($rows as $row)
    {
      $rowArray = $row->toArray();
      
      if ($rowArray['isNext'])
      {
        $test->setNext(new Application_Model_Test($rowArray));
      }
      else
      {
        $test->setPrevious(new Application_Model_Test($rowArray));
      }
    }
    
    return $test;
  }  
   
  /**
   * UWAGA! Metoda musi być uruchamina wewnątrz transakcji.
   * @param Application_Model_Test $test
   */
  public function addTest(Application_Model_Test $test)
  {
    $db = $this->_getDbTable();
    $data = array(
      'project_id'      => $test->getProjectId(),
      'status'          => $test->getStatusId(),
      'type'            => $test->getTypeId(),
      'author_id'       => $test->getAuthorId(),
      'create_date'     => date('Y-m-d H:i:s'),
      'name'            => $test->getName(),
      'description'     => $test->getDescription(),
      'current_version' => (int)$test->getCurrentVersion()
    );
    
    $test->setId($db->insert($data));
    $db->update(array('family_id' => $test->getId()), array('id = ?' => $test->getId()));
  }
  
  public function addOtherTest(Application_Model_Test $test)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $test->setStatus(Application_Model_TestStatus::ACTIVE);
    $test->setType(Application_Model_TestType::OTHER_TEST);
    
    try
    {
      $adapter->beginTransaction();
      $this->addTest($test);
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($test);
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function addTestCase(Application_Model_TestCase $testCase)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $testCase->setStatus(Application_Model_TestStatus::ACTIVE);
    $testCase->setType(Application_Model_TestType::TEST_CASE);
    
    try
    {
      $adapter->beginTransaction();
      $this->addTest($testCase);
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($testCase);
      $testCaseMapper = new Project_Model_TestCaseMapper();
      $testCaseMapper->add($testCase);
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function addExploratoryTest(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $exploratoryTest->setStatus(Application_Model_TestStatus::ACTIVE);
    $exploratoryTest->setType(Application_Model_TestType::EXPLORATORY_TEST);
    $exploratoryTest->setDescription('');
    
    try
    {
      $adapter->beginTransaction();
      $this->addTest($exploratoryTest);
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($exploratoryTest);
      $exploratoryTestMapper = new Project_Model_ExploratoryTestMapper();
      $exploratoryTestMapper->add($exploratoryTest);
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function editOtherTestCore(Application_Model_Test $testOther)
  {
    if ($testOther->isNewVersion()) 
    {
      return $this->addOtherTestVersion($testOther);
    }
    
    return $this->editOtherTest($testOther);
  }
  
  public function editOtherTest(Application_Model_Test $testOther)
  {
    $db = $this->_getDbTable();
    
    $data = array(
      'project_id'  => $testOther->getProjectId(),
      'name'        => $testOther->getName(),
      'description' => $testOther->getDescription()
    );
    
    try
    {
      $db->update($data, array('id = ?' => $testOther->getId()));
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($testOther);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function addOtherTestVersion(Application_Model_Test $test)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $dataOldVersion = array(
      'current_version' => false
    );
    
    $dataNewVersion = array(
      'project_id'      => $test->getProjectId(),
      'status'          => Application_Model_TestStatus::ACTIVE,
      'type'            => Application_Model_TestType::OTHER_TEST,
      'author_id'       => $test->getAuthorId(),
      'name'            => $test->getName(),
      'description'     => $test->getDescription(),
      'create_date'     => date('Y-m-d H:i:s'),
      'current_version' => true
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $test->getId()));
      
      $test->setId($db->insert($dataNewVersion));
      $db->update(array('family_id' => $test->getFamilyId()), array('id = ?' => $test->getId()));
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($test);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function editTestCaseCore(Application_Model_TestCase $testCase)
  {
    if ($testCase->isNewVersion()) 
    {
      return $this->addTestCaseVersion($testCase);
    }
    
    return $this->editTestCase($testCase);
  }
  
  public function editTestCase(Application_Model_TestCase $testCase)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array(
      'project_id'  => $testCase->getProjectId(),
      'name'        => $testCase->getName(),
      'description' => $testCase->getDescription()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($data, array('id = ?' => $testCase->getId()));
      
      $testCaseMapper = new Project_Model_TestCaseMapper();
      $testCaseMapper->edit($testCase);
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($testCase);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function addTestCaseVersion(Application_Model_TestCase $testCase)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $dataOldVersion = array(
      'current_version' => false
    );
    
    $dataNewVersion = array(
      'project_id'      => $testCase->getProjectId(),
      'status'          => Application_Model_TestStatus::ACTIVE,
      'type'            => Application_Model_TestType::TEST_CASE,
      'author_id'       => $testCase->getAuthorId(),
      'name'            => $testCase->getName(),
      'description'     => $testCase->getDescription(),
      'create_date'     => date('Y-m-d H:i:s'),
      'current_version' => true
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $testCase->getId()));
      
      $testCase->setId($db->insert($dataNewVersion));
      $db->update(array('family_id' => $testCase->getFamilyId()), array('id = ?' => $testCase->getId()));
      
      $testCaseMapper = new Project_Model_TestCaseMapper();
      $testCaseMapper->add($testCase);
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($testCase);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function editExploratoryTestCore(Application_Model_ExploratoryTest $exploratoryTest)
  {
    if ($exploratoryTest->isNewVersion()) 
    {
      return $this->addTestExplorationVersion($exploratoryTest);
    }
    
    return $this->editTestExploration($exploratoryTest);
  }
  
  public function editTestExploration(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array(
      'project_id' => $exploratoryTest->getProjectId(),
      'name'       => $exploratoryTest->getName()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($data, array('id = ?' => $exploratoryTest->getId()));
      
      $exploratoryTestMapper = new Project_Model_ExploratoryTestMapper();
      $exploratoryTestMapper->edit($exploratoryTest);
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($exploratoryTest);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function addTestExplorationVersion(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $dataOldVersion = array(
      'current_version' => false
    );
    
    $dataNewVersion = array(
      'project_id'      => $exploratoryTest->getProjectId(),
      'status'          => Application_Model_TestStatus::ACTIVE,
      'type'            => Application_Model_TestType::EXPLORATORY_TEST,
      'author_id'       => $exploratoryTest->getAuthorId(),
      'name'            => $exploratoryTest->getName(),
      'description'     => '',
      'create_date'     => date('Y-m-d H:i:s'),
      'current_version' => true
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $exploratoryTest->getId()));
      
      $exploratoryTest->setId($db->insert($dataNewVersion));
      $db->update(array('family_id' => $exploratoryTest->getFamilyId()), array('id = ?' => $exploratoryTest->getId()));
      
      $exploratoryTestMapper = new Project_Model_ExploratoryTestMapper();
      $exploratoryTestMapper->add($exploratoryTest);
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($exploratoryTest);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }

  public function delete(Custom_Interface_Test $test)
  {
    if (null === $test->getId())
    {
      return false;
    }
    
    $db = $this->_getDbTable();
    
    $data = array(
      'status' => Application_Model_TestStatus::DELETED
    );
    
    $where = array(
      'id = ?'      => $test->getId(),
      'project_id'  => $test->getProjectId()
    );
    
    return $db->update($data, $where) == 1;
  }
}