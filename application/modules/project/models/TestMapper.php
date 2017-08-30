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
  
  public function getAll(Zend_Controller_Request_Abstract $request, Application_Model_Project $project)
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
      $test->setProjectObject($project);
      $list[$test->getId()] = $test;
    }
    
    return array($list, $paginator);
  }
  
  public function getAllIds(Zend_Controller_Request_Abstract $request)
  {
    $rows = $this->_getDbTable()->getAllIds($request);    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[] = $row['id'];
    }
    
    return $list;
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request, Application_Model_Project $project)
  {
    return $this->_getDbTable()->getAllAjax($request, $project->getId())->toArray();
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
  
  /* get for view */
  public function getForViewInTask(Application_Model_Test $test, Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getForViewInTask($test->getId(), $project->getId());
    
    if (null === $row)
    {
      return false;
    }
    
    $test->setDbProperties($row->toArray());
    
    if ($test->getTypeId() == Application_Model_TestType::CHECKLIST)
    {
      $checklist = new Application_Model_Checklist();
      $checklist->setDbProperties($row->toArray());

      $checklistItemMapper = new Project_Model_ChecklistItemMapper();
      return $checklist->setItems($checklistItemMapper->getAllByTest($checklist));
    }
    
    return $test;
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
  
  public function getTestCaseForView(Application_Model_TestCase $testCase)
  {
    $row = $this->_getDbTable()->getTestCaseForView($testCase->getId(), $testCase->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $testCase->setDbProperties($row->toArray()); 
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
  
  public function getAutomaticTestForView(Application_Model_AutomaticTest $automaticTest)
  {
    $row = $this->_getDbTable()->getAutomaticTestForView($automaticTest->getId(), $automaticTest->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    return $automaticTest->setDbProperties($row->toArray());
  }
  
  public function getChecklistForView(Application_Model_Checklist $checklist)
  {
    $row = $this->_getDbTable()->getChecklistForView($checklist->getId(), $checklist->getProjectId());
    
    if (null === $row)
    {
      return false;
    }

    $checklistItemMapper = new Project_Model_ChecklistItemMapper();
    $checklist->setItems($checklistItemMapper->getAllByTest($checklist));

    return $checklist->setDbProperties($row->toArray());
  }
  
  /* get for edit */
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
  
  public function getAutomaticTestForEdit(Application_Model_AutomaticTest $automaticTest)
  {
    $row = $this->_getDbTable()->getAutomaticTestForEdit($automaticTest->getId(), $automaticTest->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    $rowData = $row->toArray();
    $automaticTest->setDbProperties($rowData);
    return $automaticTest->map($rowData);
  }
  
  public function getChecklistForEdit(Application_Model_Checklist $checklist)
  {
    $row = $this->_getDbTable()->getChecklistForEdit($checklist->getId(), $checklist->getProjectId());
    
    if (null === $row)
    {
      return false;
    }
    
    $rowData = $row->toArray();
    $checklist->setDbProperties($rowData);
    return $checklist->map($rowData);
  }
   
  /* add */
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
      'current_version' => (int)$test->getCurrentVersion(),
      'ordinal_no'      => 0
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
  
  public function addAutomaticTest(Application_Model_AutomaticTest $automaticTest)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $automaticTest->setStatus(Application_Model_TestStatus::ACTIVE);
    $automaticTest->setType(Application_Model_TestType::AUTOMATIC_TEST);
    
    try
    {
      $adapter->beginTransaction();
      $this->addTest($automaticTest);
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($automaticTest);
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function addChecklist(Application_Model_Checklist $checklist)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    $checklist->setStatus(Application_Model_TestStatus::ACTIVE);
    $checklist->setType(Application_Model_TestType::CHECKLIST);
    
    try
    {
      $adapter->beginTransaction();
      $this->addTest($checklist);
      $checklistItemMapper = new Project_Model_ChecklistItemMapper();
      $checklistItemMapper->save($checklist);
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($checklist);
      return $adapter->commit();
    }
    catch (Exception $e)
    {echo $e->getTraceAsString();die;
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  /* edit */
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
  
  public function editAutomaticTestCore(Application_Model_AutomaticTest $testAutomatic)
  {
    if ($testAutomatic->isNewVersion()) 
    {
      return $this->addAutomaticTestVersion($testAutomatic);
    }
    
    return $this->editAutomaticTest($testAutomatic);
  }
  
  public function editAutomaticTest(Application_Model_AutomaticTest $testAutomatic)
  {
    $db = $this->_getDbTable();
    
    $data = array(
      'project_id'  => $testAutomatic->getProjectId(),
      'name'        => $testAutomatic->getName(),
      'description' => $testAutomatic->getDescription()
    );
    
    try
    {
      $db->update($data, array('id = ?' => $testAutomatic->getId()));
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($testAutomatic);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function editChecklistCore(Application_Model_Checklist $checklist)
  {
    if ($checklist->isNewVersion()) 
    {
      return $this->addChecklistVersion($checklist);
    }
    
    return $this->editChecklist($checklist);
  }
  
  public function editChecklist(Application_Model_Checklist $checklist)
  {
    $db = $this->_getDbTable();
    
    $data = array(
      'project_id'  => $checklist->getProjectId(),
      'name'        => $checklist->getName(),
      'description' => $checklist->getDescription()
    );
    
    try
    {
      $db->update($data, array('id = ?' => $checklist->getId()));
      $checklistItemMapper = new Project_Model_ChecklistItemMapper();
      $checklistItemMapper->save($checklist);
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($checklist);
      return true;
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  /* add new version */
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
      'current_version' => true,
      'ordinal_no'      => $test->getOrdinalNo(),
      'family_id'       => $test->getFamilyId()
    );

    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $test->getId()));      
      $test->setId($db->insert($dataNewVersion));
      
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
      'current_version' => true,
      'ordinal_no'      => $testCase->getOrdinalNo(),
      'family_id'       => $testCase->getFamilyId()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $testCase->getId()));      
      $testCase->setId($db->insert($dataNewVersion));
      
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
      'current_version' => true,
      'ordinal_no'      => $exploratoryTest->getOrdinalNo(),
      'family_id'       => $exploratoryTest->getFamilyId()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $exploratoryTest->getId()));      
      $exploratoryTest->setId($db->insert($dataNewVersion));
      
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
  
  public function addAutomaticTestVersion(Application_Model_AutomaticTest $automaticTest)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $dataOldVersion = array(
      'current_version' => false
    );
    
    $dataNewVersion = array(
      'project_id'      => $automaticTest->getProjectId(),
      'status'          => Application_Model_TestStatus::ACTIVE,
      'type'            => Application_Model_TestType::AUTOMATIC_TEST,
      'author_id'       => $automaticTest->getAuthorId(),
      'name'            => $automaticTest->getName(),
      'description'     => $automaticTest->getDescription(),
      'create_date'     => date('Y-m-d H:i:s'),
      'current_version' => true,
      'ordinal_no'      => $automaticTest->getOrdinalNo(),
      'family_id'       => $automaticTest->getFamilyId()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $automaticTest->getId()));      
      $automaticTest->setId($db->insert($dataNewVersion));
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($automaticTest);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }
  
  public function addChecklistVersion(Application_Model_Checklist $checklist)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $dataOldVersion = array(
      'current_version' => false
    );
    
    $dataNewVersion = array(
      'project_id'      => $checklist->getProjectId(),
      'status'          => Application_Model_TestStatus::ACTIVE,
      'type'            => Application_Model_TestType::CHECKLIST,
      'author_id'       => $checklist->getAuthorId(),
      'name'            => $checklist->getName(),
      'description'     => $checklist->getDescription(),
      'create_date'     => date('Y-m-d H:i:s'),
      'current_version' => true,
      'ordinal_no'      => $checklist->getOrdinalNo(),
      'family_id'       => $checklist->getFamilyId()
    );
    
    try
    {
      $adapter->beginTransaction();
      
      $db->update($dataOldVersion, array('id = ?' => $checklist->getId()));      
      $checklist->setId($db->insert($dataNewVersion));

      $checklistItemMapper = new Project_Model_ChecklistItemMapper();
      $checklistItemMapper->save($checklist);
      
      $attachmentMapper = new Project_Model_AttachmentMapper();
      $attachmentMapper->saveTest($checklist);
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollback();
      return false;
    }
  }

  /* delete */
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
  
  public function deleteByIds(array $testIds)
  {
    if (count($testIds) == 0)
    {
      return true;
    }
    
    $db = $this->_getDbTable();
    
    $data = array(
      'status' => Application_Model_TestStatus::DELETED
    );
    
    $where = array(
      'id IN(?)'  => $testIds
    );
    
    $db->update($data, $where);
    return true;
  }
  
  public function getByIds4CheckAccess(array $ids)
  {
    if (count($ids) === 0)
    {
      return array();
    }
    
    $rows = $this->_getDbTable()->getByIds4CheckAccess($ids);
    
    if (null === $rows)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = new Application_Model_Test($row);
    }

    return $list;
  }
}