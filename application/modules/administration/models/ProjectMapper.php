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
class Administration_Model_ProjectMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_ProjectDbTable';
  
  public function getAll(Zend_Controller_Request_Abstract $request)
  {
    $db = $this->_getDbTable();
    
    $adapter = new Zend_Paginator_Adapter_DbSelect($db->getSqlAll($request));
    $adapter->setRowCount($db->getSqlAllCount($request));
 
    $paginator = new Zend_Paginator($adapter);
    $paginator->setCurrentPageNumber($request->getParam('page', 1));
    $resultCountPerPage = (int)$request->getParam('resultCountPerPage');
    $paginator->setItemCountPerPage($resultCountPerPage > 0 ? $resultCountPerPage : 10);
    
    $list = array();
    
    foreach ($paginator->getCurrentItems() as $row)
    {
      $project = new Application_Model_Project();
      $list[] = $project->setDbProperties($row);
    }

    return array($list, $paginator);
  }
  
  public function getAllAjax(Zend_Controller_Request_Abstract $request)
  {
    return $this->_getDbTable()->getAllAjax($request)->toArray();
  }
  
  public function getById(Application_Model_Project $project, $returnRowData = false )
  {
    $row = $this->_getDbTable()->getById($project->getId());
    
    if ( null === $row )
    {
      return false;
    }
    
    if ( $returnRowData )
    {
      return $row->toArray();
    }
    
    return $project->setDbProperties($row->toArray());
  }
  
  public function getForPopulateByIds(array $ids, $returnRowData = false)
  {
    $result = $this->_getDbTable()->getForPopulateByIds($ids);
    
    if ($returnRowData)
    {
      return $result->toArray();
    }
    
    return $result;
  }
  
  public function add(Application_Model_Project $project)
  {
    $db             = $this->_getDbTable();
    $adapter        = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();
      $project->setStatus(Application_Model_ProjectStatus::ACTIVE);

      $data = array(
        'prefix'                    => $project->getPrefix(),
        'status'                    => $project->getStatusId(),
        'create_date'               => date('Y-m-d H:i:s'),
        'name'                      => $project->getName(),
        'description'               => $project->getDescription(),
        'open_status_color'         => $project->getOpenStatusColor(),
        'in_progress_status_color'  => $project->getInProgressStatusColor()
      );

      $id = $db->insert($data);
      $project->setId($id);
      
      $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
      $projectBugTrackerMapper->addInternal($project);
      
      $resolutionMapper = new Administration_Model_ResolutionMapper();
      
      foreach ($project->getResolutions() as $resolution)
      {
        $resolutionMapper->add($resolution);
      }
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      $adapter->rollBack();
      return false;
    }
  }

  public function getForEdit(Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getForEdit($project->getId());
    
    if (null === $row)
    {
      return false;
    }

    return $project->map($row->toArray());
  }
  
  public function save(Application_Model_Project $project)
  {
    $db             = $this->_getDbTable();
    $adapter        = $db->getAdapter();
    
    try
    {
      $adapter->beginTransaction();

      $data = array(
        'prefix'                    => $project->getPrefix(),
        'name'                      => $project->getName(),
        'description'               => $project->getDescription(),
        'open_status_color'         => $project->getOpenStatusColor(),
        'in_progress_status_color'  => $project->getInProgressStatusColor()
      );

      $db->update($data, array('id = ?' => $project->getId()));
      
      return $adapter->commit();
    }
    catch (Exception $e)
    {
      $adapter->rollback();
      throw $e;
    }
  }
  
  public function activate(Application_Model_Project $project)
  {
    if ($project->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_ProjectStatus::ACTIVE
    );
    
    $where = array(
      'status = ?' => Application_Model_ProjectStatus::SUSPENDED,
      'id = ?'      => $project->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function suspend(Application_Model_Project $project)
  {
    if ($project->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_ProjectStatus::SUSPENDED
    );
    
    $where = array(
      'status = ?' => Application_Model_ProjectStatus::ACTIVE,
      'id = ?'     => $project->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }
  
  public function finish(Application_Model_Project $project)
  {
    if ($project->getId() === null)
    {
      return false;
    }
    
    $data = array(
      'status' => Application_Model_ProjectStatus::FINISHED
    );
    
    $where = array(
      'status != ?' => Application_Model_ProjectStatus::FINISHED,
      'id = ?'      => $project->getId()
    );
    
    return $this->_getDbTable()->update($data, $where) == 1;
  }  
  
  public function getForView(Application_Model_Project $project)
  {
    $row = $this->_getDbTable()->getForView($project->getId());
    
    if ($row === null)
    {
      return false;
    }
    
    return $project->setDbProperties($row->toArray());
  }
  
  public function getNotFinishedAllAsOptions()
  {
    $rows = $this->_getDbTable()->getNotFinishedAllAsOptions();
    
    if ($rows === null)
    {
      return false;
    }
    
    $list = array();
    
    foreach ($rows->toArray() as $row)
    {
      $list[$row['id']] = $row['name'];
    }
    
    return $list;
  }
  
  public function export(Application_Model_Project $project)
  {
    /**** Obiekt File ****/
    $file = new Application_Model_File();
    $file->setDates(1);
    $file->setName($project->getName().'_'.date('Ymd_His', strtotime($file->getCreateDate())));
    $file->setExtension('zip');
    $file->setSubpath();
    $file->setDescription($project->getExtraData('fileDescription'));

    $tempIniFilePath = $file->getNewFullPath(Utils_Text::generateToken());
    
    $csvFiles = array();
    $data = array(
      'description'               => $project->getDescription(),
      'open_status_color'         => $project->getOpenStatusColor(),
      'in_progress_status_color'  => $project->getInProgressStatusColor(),
      'csvFile'                   => array() 
    );
    
    /**** project bug tracker ****/
    $data['csvFile'][] = 'project_bug_tracker.csv';
    $csvFiles['project_bug_tracker.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
    $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
    $rows = $projectBugTrackerMapper->getForExportByProject($project);
    
    if ($rows === false)
    {
      $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
      return false;
    }

    $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['project_bug_tracker.csv'], $this->_prepareCsvColumnsToExport($rows));
    $csvFile->writeMany($rows);
    $csvFile->close();

    if ($project->getExtraData('exportEnvironments'))
    {
      /**** Środowiska ****/
      $data['csvFile'][] = 'environment.csv';
      $csvFiles['environment.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $environmentMapper = new Administration_Model_EnvironmentMapper();
      $rows = $environmentMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['environment.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
    }

    if ($project->getExtraData('exportVersions'))
    {
      /**** Wersje ****/
      $data['csvFile'][] = 'versions.csv';
      $csvFiles['versions.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $versionMapper = new Administration_Model_VersionMapper();
      $rows = $versionMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['versions.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
    }

    if ($project->getExtraData('exportTags'))
    {
      /**** Tagi ****/
      $data['csvFile'][] = 'tags.csv';
      $csvFiles['tags.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $tagMapper = new Administration_Model_TagMapper();
      $rows = $tagMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['tags.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
    }
    
    if ($project->getExtraData('exportReleases'))
    {
      /**** Wydania ****/
      $data['csvFile'][] = 'release.csv';
      $csvFiles['release.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $releaseMapper = new Administration_Model_ReleaseMapper();
      $rows = $releaseMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['release.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
    }

    if ($project->getExtraData('exportUsers') && ($project->getExtraData('exportRoles') || $project->getExtraData('exportTasks')))
    {
      /**** Użytkownicy ****/
      $data['csvFile'][] = 'user.csv';
      $csvFiles['user.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $userMapper = new Administration_Model_UserMapper();
      $rows = $userMapper->getForExportByProject($project, $project->getExtraData('exportRoles'), $project->getExtraData('exportTasks'));

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['user.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
    }

    if ($project->getExtraData('exportRoles'))
    {
      /**** Role ****/
      $data['csvFile'][] = 'role.csv';
      $csvFiles['role.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $roleMapper = new Administration_Model_RoleMapper();
      $rows = $roleMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['role.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();

      /**** Ustawienia ról ****/
      $data['csvFile'][] = 'role_setting.csv';
      $csvFiles['role_setting.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $roleSettingMapper = new Administration_Model_RoleSettingMapper();
      $rows = $roleSettingMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['role_setting.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();

      if ($project->getExtraData('exportUsers'))
      {
        /**** Role użytkowników ****/
        $data['csvFile'][] = 'role_user.csv';
        $csvFiles['role_user.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
        $roleUserMapper = new Administration_Model_RoleUserMapper();
        $rows = $roleUserMapper->getForExportByProject($project);

        if ($rows === false)
        {
          $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
          return false;
        }

        $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['role_user.csv'], $this->_prepareCsvColumnsToExport($rows));
        $csvFile->writeMany($rows);
        $csvFile->close();
      }
    }

    if ($project->getExtraData('exportTasks'))
    {
      /**** Zadania inne ****/
      $data['csvFile'][] = 'task.csv';
      $csvFiles['task.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $taskMapper = new Administration_Model_TaskMapper();
      $rows = $taskMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['task.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
      
      /**** Przypadki testowe ****/
      $data['csvFile'][] = 'task_test_case.csv';
      $csvFiles['task_test_case.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $tasTestCasekMapper = new Administration_Model_TaskTestCaseMapper();
      $rows = $tasTestCasekMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['task_test_case.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
      
      /**** Eksploracja ****/
      $data['csvFile'][] = 'task_exploration.csv';
      $csvFiles['task_exploration.csv'] = $file->getNewFullPath(Utils_Text::generateToken());
      $taskExplorationMapper = new Administration_Model_TaskExplorationMapper();
      $rows = $taskExplorationMapper->getForExportByProject($project);

      if ($rows === false)
      {
        $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
        return false;
      }

      $csvFile = new Utils_File_Writer_Table_Csv($csvFiles['task_exploration.csv'], $this->_prepareCsvColumnsToExport($rows));
      $csvFile->writeMany($rows);
      $csvFile->close();
    }
    
    /**** Zapis pliku INI ****/
    Utils_File_Ini::write($tempIniFilePath, $data, false);
    
    /**** Kompresja plików do ZIP ****/
    $zip = new ZipArchive();
    
    if ($zip->open($file->getFullPath(true), ZipArchive::CREATE) === true)
    {
      $zip->addFile($tempIniFilePath, 'data.ini');
      
      foreach ($csvFiles as $fileName => $csvFilePath)
      {
        $zip->addFile($csvFilePath, $fileName);
      }
      
      $zip->close();
    }
    else
    {
      $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
      return false;
    }

    /**** Dodawanie pliku ****/
    $fileMapper = new Application_Model_FileMapper();
    
    if ($fileMapper->add($file))
    {
      $project->setExtraData('fileId', $file->getId());
      $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
      return true;
    }

    $this->_removeTempFilesForExport($csvFiles, $tempIniFilePath);
    return false;
  }
  
  private function _removeTempFilesForExport(array $csvFiles, $tempIniFilePath)
  {
    // Usuwanie plików tymczasowych    
    @unlink($tempIniFilePath);

    foreach ($csvFiles as $csvFile)
    {
      @unlink($csvFile);
    }
  }
  
  private function _prepareCsvColumnsToExport(array $rows)
  {
    $columns = array();
    
    foreach ($rows as $row)
    {
      foreach (array_keys($row) as $columnName)
      {
        $columns[$columnName] = $columnName;
      }
      
      break;
    }
    
    return $columns;
  }
  
  public function import(Application_Model_Project $project)
  {
    set_time_limit(0);
    $fileName = $project->getExtraData('file');
    $filePath = _TEMP_PATH.DIRECTORY_SEPARATOR.$fileName;
    $buf = explode('.', $fileName);
    $extractPath = _TEMP_PATH.DIRECTORY_SEPARATOR.$buf[0];
    
    /**** Rozpakowanie pliku ZIP ****/
    $zip = new ZipArchive;
    $zipFileNameList = array();
    
    if ($zip->open($filePath))
    {
      for ($i = 0; $i < $zip->numFiles; $i++)
      {
        $zipFileNameList[] = $zip->getNameIndex($i);
      }
      
      @mkdir($extractPath);
      $zip->extractTo($extractPath);
      $zip->close();
      @unlink($filePath);
      
      /**** Wczytanie pliku INI ****/
      if (!file_exists($extractPath.DIRECTORY_SEPARATOR.'data.ini'))
      {
        $this->_removeTempFilesForImport($extractPath, $zipFileNameList);
        return false;
      }

      $data = parse_ini_file($extractPath.DIRECTORY_SEPARATOR.'data.ini');
      
      if ($data === false)
      {
        $this->_removeTempFilesForImport($extractPath, $zipFileNameList);
        return false;
      }

      /**** Sprawdzenie plików ****/
      foreach ($data['csvFile'] as $csvFile)
      {
        if (!file_exists($extractPath.DIRECTORY_SEPARATOR.$csvFile))
        {
          $this->_removeTempFilesForImport($extractPath, $zipFileNameList);
          return false;
        }
      }

      $db = $this->_getDbTable();
      $adapter = $db->getAdapter();
      
      try
      {
        $adapter->beginTransaction();
        
        /**** Projekt ****/
        $id = $this->_getDbTable()->insert(array(
          'prefix'                    => $project->getPrefix(),
          'status'                    => Application_Model_ProjectStatus::ACTIVE,
          'create_date'               => date('Y-m-d H:i:s'),
          'name'                      => $project->getName(),
          'description'               => $data['description'],
          'open_status_color'         => $data['open_status_color'],
          'in_progress_status_color'  => $data['in_progress_status_color']
        ));
        
        if ($id <= 0)
        {
          throw new Exception('[Project import]Insert project error!');
        }
        
        $project->setId($id);
        $resolutionMapper = new Administration_Model_ResolutionMapper();

        foreach ($project->getResolutions() as $resolution)
        {
          $resolutionMapper->add($resolution);
        }        
        
        /**** Project bug tracker ****/
        if (($index = array_search('project_bug_tracker.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $projectBugTrackers = array();
          
          while (($row = $csv->read()) !== false)
          {
            $row['bug_tracker_id'] = empty($row['bug_tracker_id']) ? null : $row['bug_tracker_id'];
            $projectBugTrackers[$row['id']] = $row;
          }
          
          $csv->close();
          $projectBugTrackerMapper = new Administration_Model_ProjectBugTrackerMapper();
          $projectBugTrackerMapper->addForImport($project, $projectBugTrackers);
        }

        /**** Środowiska ****/
        if (($index = array_search('environment.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $rows = $csv->readAll();
          $csv->close();
          $environmentMapper = new Administration_Model_EnvironmentMapper();
          $environmentMapper->addForImport($project, $rows);
        }

        /**** Wersje ****/
        if (($index = array_search('versions.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $rows = $csv->readAll();
          $csv->close();
          $versionMapper = new Administration_Model_VersionMapper();
          $versionMapper->addForImport($project, $rows);
        }

        /**** Tagi ****/
        if (($index = array_search('tags.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $rows = $csv->readAll();
          $csv->close();
          $tagMapper = new Administration_Model_TagMapper();
          $tagMapper->addForImport($project, $rows);
        }

        /**** Użytkownicy ****/
        if (($index = array_search('user.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $users = array();
          $emails = array();
          $date = date('Y-m-d H:i:s');
          $user = new Application_Model_User();
          $user->setPassword($project->getExtraData('password'));
          
          while (($row = $csv->read()) !== false)
          {
            $emails[$row['id']] = $row['email'];
            $row['create_date'] = $date;
            $row['status'] = Application_Model_UserStatus::ACTIVE;
            $row['reset_password'] = 1;
            $row['password'] = $user->getPassword();
            $row['salt'] = $user->getSalt();
            $users[$row['id']] = $row;
            unset($users[$row['id']]['id']);
          }
          
          $csv->close();
          $userMapper = new Administration_Model_UserMapper();
          $users = $userMapper->addForImport($users, $emails);
        }

        /**** Wydania ****/
        if (($index = array_search('release.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $releases = array();
          
          while (($row = $csv->read()) !== false)
          {
            $row['project_id'] = $project->getId();
            $row['name'] = empty($row['name']) ? null : $row['name'];
            $releases[$row['id']] = $row;
            unset($releases[$row['id']]['id']);
          }
        
          $csv->close();
          $releaseMapper = new Administration_Model_ReleaseMapper();
          $releases = $releaseMapper->addForImport($releases);
        }

        /**** Role ****/
        if (($index = array_search('role.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $roles = array();
          
          while (($row = $csv->read()) !== false)
          {
            $row['project_id'] = $project->getId();
            $roles[$row['id']] = $row;
            unset($roles[$row['id']]['id']);
          }
            
          $csv->close();
          $roleMapper = new Administration_Model_RoleMapper();
          $roles = $roleMapper->addForImport($roles);

          /**** Ustawienia ról ****/
          if (($index = array_search('role_setting.csv', $data['csvFile'])) !== false)
          {
            $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
            $roleSettings = array();

            while (($row = $csv->read()) !== false)
            {
              $row['role_id'] = $roles[$row['role_id']]['id'];
              $roleSettings[] = $row;
            }
          
            $csv->close();
            $roleSettingMapper = new Administration_Model_RoleSettingMapper();
            $roleSettingMapper->addForImport($roleSettings);
          }

          /**** Użytkownicy ról ****/
          if (in_array('user.csv', $data['csvFile']) && ($index = array_search('role_user.csv', $data['csvFile'])) !== false)
          {
            $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
            $rows = array();

            while (($row = $csv->read()) !== false)
            {
              $row['role_id'] = $roles[$row['role_id']]['id'];
              $row['user_id'] = $users[$row['user_id']]['id'];
              $rows[] = $row;
            }
          
            $csv->close();
            $roleUserMapper = new Administration_Model_RoleUserMapper();
            $roleUserMapper->addForImport($rows);
          }
        }

        /**** Zadania ****/
        if (in_array('user.csv', $data['csvFile']) && ($index = array_search('task.csv', $data['csvFile'])) !== false)
        {
          $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
          $tasks = array();
          $date = date('Y-m-d H:i:s');

          while (($row = $csv->read()) !== false)
          {
            $row['ordinal_no'] = array_key_exists('ordinal_no', $row) ? $row['ordinal_no'] : 0;
            $row['project_id'] = $project->getId();
            $row['author_id'] = $users[$row['author_id']]['id'];
            $row['create_date'] = $date;
            $row['status'] = Application_Model_TaskStatus::ACTIVE;
            $row['family_id'] = 1;
            $row['current_version'] = 1;
            $tasks[$row['id']] = $row;
            unset($tasks[$row['id']]['id']);
          }

          $csv->close();
          $taskMapper = new Administration_Model_TaskMapper();
          $tasks = $taskMapper->addForImport($tasks);

          // Przypadki testowe
          if (($index = array_search('task_test_case.csv', $data['csvFile'])) !== false)
          {
            $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
            $rows = array();
            $date = date('Y-m-d H:i:s');

            while (($row = $csv->read()) !== false)
            {
              $row['task_id'] = $tasks[$row['task_id']]['id'];
              $rows[] = $row;
            }

            $csv->close();
            $taskTestCaseMapper = new Administration_Model_TaskTestCaseMapper();
            $taskTestCaseMapper->addForImport($rows);
          }

          // Eksploracja
          if (($index = array_search('task_exploration.csv', $data['csvFile'])) !== false)
          {
            $csv = new Utils_File_Reader_Table_Csv($extractPath.DIRECTORY_SEPARATOR.$data['csvFile'][$index]);
            $rows = array();
            $date = date('Y-m-d H:i:s');

            while (($row = $csv->read()) !== false)
            {
              $row['task_id'] = $tasks[$row['task_id']]['id'];
              $rows[] = $row;
            }

            $csv->close();
            $taskExplorationMapper = new Administration_Model_TaskExplorationMapper();
            $taskExplorationMapper->addForImport($rows);
          }
        }

        $adapter->commit();
      }
      catch (Exception $e)
      {
        Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
        $adapter->rollBack();
        $this->_removeTempFilesForImport($extractPath, $zipFileNameList);
        return false;
      }
      
      foreach ($data['csvFile'] as $csvFile)
      {
        $csvFilePath = $extractPath.DIRECTORY_SEPARATOR.$csvFile;
        $csv = new Utils_File_Reader_Table_Csv($csvFilePath);
        $rows = array();
        $index = 0;
        
        while (($row = $csv->read()) !== false)
        {
          $rows[array_key_exists('id', $row) ? $row['id'] : $index++] = $row;
        }
        
        $csv->close();
        var_dump($rows);
        @unlink($csvFilePath);
      }
      
      $this->_removeTempFilesForImport($extractPath, $zipFileNameList);
      return true;
    }

    return false;
  }
  
  private function _removeTempFilesForImport($extractPath, array $fileNameList)
  {
    foreach ($fileNameList as $fileName)
    {
      @unlink($extractPath.DIRECTORY_SEPARATOR.$fileName);
    }
    
    rmdir($extractPath);
  }
  
  public function checkIfExists($projectId)
  {
    return (bool) $this->_getDbTable()->checkIfExists($projectId);
  }
}