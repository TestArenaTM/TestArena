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
abstract class Custom_Model_DbTable_Criteria_Abstract extends Custom_Model_DbTable_Abstract
{
  private $_request = null;
  
  protected function _setOrderConditions(Zend_Db_Table_Select $select, Zend_Controller_Request_Abstract $request)
  {
    $this->_setRequest($request);
    $tableName = $select->getTable()->info('name');

    if ($this->_paramExists('column') && preg_match('/^'.$tableName.'_.+/', $request->getParam('column')))
    {
      $columnName = preg_replace('/^'.$tableName.'_/', '', $request->getParam('column'));
      $direction = $this->_paramExists('sort') ? $request->getParam('sort') : 'ASC';
      
      if (in_array($columnName, array('firstname', 'lastname', 'email', 'organization', 'department', 'title')))
      {
        $select->order(new Zend_Db_Expr($columnName.' COLLATE utf8_polish_ci '.$direction.', id '.$direction));
      }
      else
      {
        $select->order(new Zend_Db_Expr($columnName.' '.$direction.', id '.$direction));
      }
    }
  }
  
  protected function _setWhereCriteria(Zend_Db_Table_Select $select, Zend_Controller_Request_Abstract $request)
  {
    $this->_setRequest($request);
    
    switch ($select->getTable()->info('name'))
    {
      case 'project':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("p.name LIKE ? COLLATE utf8_polish_ci ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q'))))); 
        }

        if ($this->_paramExists('userId', 0))
        {
          $select->join(array('r' => 'role'), 'p.id = r.project_id', array())
            ->join(array('ru' => 'role_user'), 'r.id = ru.role_id', array())
            ->where($this->_db->quoteInto('ru.user_id = ?', $request->getParam('userId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select
            ->where($this->_db->quoteInto("CONCAT(p.name, p.prefix) LIKE ? COLLATE utf8_unicode_ci ESCAPE '='", $params));
        }
        
        if ($this->_paramExists('status') && $request->getParam('status') > 0)
        {
          $select->where($this->_db->quoteInto("p.status = ?", array($request->getParam('status')))) ;
        }
        
        if ($this->_paramExists('public') && $request->getParam('public') >= 0)
        {
          $select->where($this->_db->quoteInto("p.public = ?", array($request->getParam('public')))) ;
        }
        break;
        
      case 'release':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("r.name LIKE ? ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q'))))); 
        }

        if ($this->_paramExists('projectId', 0))
        {
          $select->where($this->_db->quoteInto('r.project_id = ?', $request->getParam('projectId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select
            ->where($this->_db->quoteInto("r.name LIKE ? ESCAPE '='", $params));
        }
        break;
        
      case 'user':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("CONCAT(u.email, u.firstname, ' ', u.lastname) LIKE ? ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q')))))
            ->group('u.id');          
        }
        
        if ($this->_paramExists('projectId', 0))
        {
          $select->where($this->_db->quoteInto('r.project_id = ?', $request->getParam('projectId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select
            ->where($this->_db->quoteInto("CONCAT(u.firstname, u.lastname, u.email, u.organization, u.department) LIKE ? ESCAPE '='", $params));
        }
        
        if ($this->_paramExists('status') && $request->getParam('status') > 0)
        {
          $select->where($this->_db->quoteInto("u.status = ?", array($request->getParam('status')))) ;
        }
        
        if ($this->_paramExists('administrator') && $request->getParam('administrator') > 0)
        {
          $select->where('u.administrator = ?', $request->getParam('administrator') == 2 ? 0 : 1) ;
        }
        break;
        
      case 'role':
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select
            ->where($this->_db->quoteInto("r.name LIKE ? ESCAPE '='", $params));
        }
        
        if ($this->_paramExists('type') && $request->getParam('type') > 0)
        {
          $select->where($this->_db->quoteInto("r.type = ?", array($request->getParam('type')))) ;
        }
        
        if ($this->_paramExists('project') && $request->getParam('project') > 0)
        {
          $select->where($this->_db->quoteInto("r.project_id = ?", array($request->getParam('project')))) ;
        }
        break;
        
      case 'task':
        if ($this->_paramExists('projectId', 0))
        {
          $select->where($this->_db->quoteInto('t.project_id = ?', $request->getParam('projectId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select->where($this->_db->quoteInto("t.title LIKE ? ESCAPE '='", $params));
        }
        
        if ($this->_paramExists('status') && ($status = $request->getParam('status')) > 0)
        {
          $select->where('t.status = ?', $status);
        }
        
        if ($this->_paramExists('priority') && $request->getParam('priority') > 0)
        {
          $select->where($this->_db->quoteInto("t.priority = ?", array($request->getParam('priority')))) ;
        }
        
        if ($this->_paramExists('release'))
        {
          $releaseId = $request->getParam('release');
          
          if ($releaseId > 0)
          {
            $select->where('t.release_id = ?', $releaseId);
          }
          elseif ($releaseId < 0)
          {
            $select->where('t.release_id IS NULL');
          }
        }
        
        if ($this->_paramExists('exceededDueDate') && $request->getParam('exceededDueDate') == 1)
        {
          $select
            ->where('t.due_date < ?', date('Y-m-d H:i:s'))
            ->where('t.status != ?', Application_Model_TaskStatus::CLOSED);
        }
        
        if ($this->_paramExists('assigner') && $request->getParam('assigner') > 0)
        {
          $select->where($this->_db->quoteInto("t.assigner_id = ?", array($request->getParam('assigner'))));
        }
        
        if ($this->_paramExists('assignee') && $request->getParam('assignee') > 0)
        {
          $select->where($this->_db->quoteInto("t.assignee_id = ?", array($request->getParam('assignee'))));
        }
        
        if ($this->_paramExists('assignerId') && $request->getParam('assignerId') > 0)
        {
          $select->where($this->_db->quoteInto("t.assigner_id = ?", array($request->getParam('assignerId'))));
        }
        
        if ($this->_paramExists('assigneeId') && $request->getParam('assigneeId') > 0)
        {
          $select->where($this->_db->quoteInto("t.assignee_id = ?", array($request->getParam('assigneeId'))));
        }
        
        if ($this->_paramExists('environment') && $request->getParam('environment') > 0)
        {
          $select->where('te.environment_id = ?', $request->getParam('environment'));
        }
        
        if ($this->_paramExists('version') && $request->getParam('version') > 0)
        {
          $select->where('tv.version_id = ?', $request->getParam('version'));
        }
        
        if ($this->_paramExists('tags') && strlen($request->getParam('tags')) > 0)
        {
          $sql = '(SELECT COUNT(*) FROM task_tag AS f_tt WHERE f_tt.task_id = t.id AND f_tt.tag_id IN('.$request->getParam('tags').'))';
          $select
            ->columns(array('tagCount' => new Zend_Db_Expr($sql)))
            ->having('tagCount = ?', count(explode(',', $request->getParam('tags'))));
        }
        break;
        
      case 'defect':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("d.title LIKE ? ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q'))))); 
        }

        if ($this->_paramExists('projectId', 0))
        {
          $select->where($this->_db->quoteInto('d.project_id = ?', $request->getParam('projectId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select->where($this->_db->quoteInto("d.title LIKE ? ESCAPE '='", $params));
        }
        
        if ($this->_paramExists('status') && ($status = $request->getParam('status')) > 0)
        {
          $select->where('d.status = ?', $status);
        }
        
        if ($this->_paramExists('priority') && $request->getParam('priority') > 0)
        {
          $select->where($this->_db->quoteInto("d.priority = ?", array($request->getParam('priority')))) ;
        }
        
        if ($this->_paramExists('release'))
        {
          $releaseId = $request->getParam('release');
          
          if ($releaseId > 0)
          {
            $select->where('d.release_id = ?', $releaseId);
          }
          elseif ($releaseId < 0)
          {
            $select->where('d.release_id IS NULL');
          }
        }
        
        if ($this->_paramExists('assigner') && $request->getParam('assigner') > 0)
        {
          $select->where($this->_db->quoteInto("d.assigner_id = ?", array($request->getParam('assigner'))));
        }
        
        if ($this->_paramExists('assignee') && $request->getParam('assignee') > 0)
        {
          $select->where($this->_db->quoteInto("d.assignee_id = ?", array($request->getParam('assignee'))));
        }
        
        if ($this->_paramExists('assignerId') && $request->getParam('assignerId') > 0)
        {
          $select->where($this->_db->quoteInto("d.assigner_id = ?", array($request->getParam('assignerId'))));
        }
        
        if ($this->_paramExists('assigneeId') && $request->getParam('assigneeId') > 0)
        {
          $select->where($this->_db->quoteInto("d.assignee_id = ?", array($request->getParam('assigneeId'))));
        }
        
        if ($this->_paramExists('environment') && $request->getParam('environment') > 0)
        {
          $select->where('de.environment_id = ?', $request->getParam('environment'));
        }
        
        if ($this->_paramExists('version') && $request->getParam('version') > 0)
        {
          $select->where('dv.version_id = ?', $request->getParam('version'));
        }
        
        if ($this->_paramExists('tags') && strlen($request->getParam('tags')) > 0)
        {
          $sql = '(SELECT COUNT(*) FROM defect_tag AS f_dt WHERE f_dt.defect_id = d.id AND f_dt.tag_id IN('.$request->getParam('tags').'))';
          $select
            ->columns(array('tagCount' => new Zend_Db_Expr($sql)))
            ->having('tagCount = ?', count(explode(',', $request->getParam('tags'))));
        }
        break;
        
      case 'test':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("t.name LIKE ? ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q')))));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select->where($this->_db->quoteInto("t.name LIKE ? ESCAPE '='", $params));
        }
        
        if ($this->_paramExists('type') && $request->getParam('type') > 0)
        {
          $select->where($this->_db->quoteInto("t.type = ?", array($request->getParam('type')))) ;
        }
        
        if ($this->_paramExists('author') && $request->getParam('author') > 0)
        {
          $select->where($this->_db->quoteInto("t.author_id = ?", array($request->getParam('author'))));
        }
        break;
        
      case 'environment':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("e.name LIKE ? ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q'))))); 
        }

        if ($this->_paramExists('projectId', 0))
        {
          $select->where($this->_db->quoteInto('e.project_id = ?', $request->getParam('projectId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select
            ->where($this->_db->quoteInto("e.name LIKE ? ESCAPE '='", $params));
        }
        break;
        
      case 'version':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("v.name LIKE ? ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q'))))); 
        }

        if ($this->_paramExists('projectId', 0))
        {
          $select->where($this->_db->quoteInto('v.project_id = ?', $request->getParam('projectId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select
            ->where($this->_db->quoteInto("v.name LIKE ? ESCAPE '='", $params));
        }
        break;
        
      case 'tag':
        if ($this->_paramExists('q'))
        {
          $select->where($this->_db->quoteInto("t.name LIKE ? ESCAPE '='", array($this->_prepareLikePhrase($request->getParam('q'))))); 
        }

        if ($this->_paramExists('projectId', 0))
        {
          $select->where($this->_db->quoteInto('t.project_id = ?', $request->getParam('projectId')));
        }
        
        if ($this->_paramExists('search') && mb_strlen($request->getParam('search'), 'UTF-8'))
        {
          $params = array($this->_prepareLikePhrase($request->getParam('search')));
          $select
            ->where($this->_db->quoteInto("t.name LIKE ? ESCAPE '='", $params));
        }
        break;
    }
  }

  
  protected function _paramExists($paramName, $excludedValue = null)
  {
    $value = $this->_getRequest()->getParam($paramName, null);
    
    if (null === $value || '' === $value || $excludedValue == $value)
    {
      return false;
    }
    
    return true;
  }
  
  private function _prepareMatchPhrase($phrase)
  {
    return Utils_Search::map2ShadowFulltext($phrase).'*';
  }
  
  private function _prepareLikePhrase($phrase)
  {
    return '%'.$this->_likeEscape($phrase, '=').'%';
  }
  
  private function _prepareLikePhraseFullText($phrase)
  {
    return '%'.$this->_likeEscape($phrase, '=').'%';
  }
  
  private function _prepareLikePhraseEnding($phrase)
  {
    return $this->_likeEscape($phrase, '=').'%';
  }
  
  private function _likeEscape($phrase, $escapeSign)
  {
    return str_replace(array($escapeSign, '_', '%'), array($escapeSign.$escapeSign, $escapeSign.'_', $escapeSign.'%'), $phrase);
  }
  
  protected function _getRequest()
  {
    return $this->_request;
  }
  
  protected function _setRequest(Zend_Controller_Request_Abstract $request)
  {
    if (null === $this->_request)
    {
      $this->_request = $request;
    }
    
    return $this;
  }
  
  protected function _setNotificationObjectCondition(Zend_Db_Table_Select $select, Custom_Interface_Notification $notification)
  {
    switch ($notification->getNotificationRule()->getType()->getId())
    {
      case Application_Model_NotificationRuleType::EXAM:
        $select->join(array('etu' => 'exam_term_user'), 'etu.user_id = u.id', array())
               ->where('etu.exam_id = ?', $notification->getObject()->getId())
               ->group('u.id');
        
        if ($notification instanceof Application_Model_AutomaticNotification)
        {
          $select->join(array('et' => 'exam_term'), 'etu.exam_term_id = et.id', array())
                 ->join(array('an' => 'automatic_notification'), 'etu.exam_id = an.object_id', array())
                 ->where('DATE_ADD(et.date, INTERVAL an.offset DAY) = CURDATE()')
                 ->where('an.id = ?', $notification->getId());
        }
        break;
      case Application_Model_NotificationRuleType::TRAINING:
        $select->join(array('ttu' => 'training_term_user'), 'ttu.user_id = u.id', array())
               ->join(array('tt' => 'training_term'), 'tt.id = ttu.training_term_id', array())
               ->where('tt.training_id = ?', $notification->getObject()->getId())
               ->group('u.id');
        
        if ($notification instanceof Application_Model_AutomaticNotification)
        {
          $select->join(array('an' => 'automatic_notification'), 'tt.training_id = an.object_id', array())
                 ->where('DATE_ADD(tt.start_date, INTERVAL an.offset DAY) = CURDATE()')
                 ->where('an.id = ?', $notification->getId());
        }
        break;
      case Application_Model_NotificationRuleType::EXAM_TERM:
        $select->join(array('etu' => 'exam_term_user'), 'etu.user_id = u.id', array())
               ->where('etu.exam_term_id = ?', $notification->getObject()->getId())
               ->group('u.id');
        
        if ($notification instanceof Application_Model_AutomaticNotification)
        {
          $select->join(array('an' => 'automatic_notification'), 'etu.exam_term_id = an.object_id', array())
                 ->where('an.id = ?', $notification->getId());
        }
        break;
      case Application_Model_NotificationRuleType::TRAINING_TERM:
        $select->join(array('ttu' => 'training_term_user'), 'ttu.user_id = u.id', array())
               ->where('ttu.training_term_id = ?', $notification->getObject()->getId())
               ->group('u.id');
        
        
        if ($notification instanceof Application_Model_AutomaticNotification)
        {
          $select->join(array('an' => 'automatic_notification'), 'ttu.training_term_id = an.object_id', array())
                 ->where('an.id = ?', $notification->getId());
        }
        break;
    }
  }
}