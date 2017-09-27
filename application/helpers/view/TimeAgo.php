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
class Zend_View_Helper_TimeAgo extends Zend_View_Helper_Abstract
{
  const MOMENT_AGO  = 'MomentAgo';
  const MINUTE_AGO  = 'MinuteAgo';
  const HOUR_AGO    = 'HourAgo';
  const DAY_AGO     = 'DayAgo';
  const WEEK_AGO    = 'WeekAgo';
  const MONTH_AGO   = 'MonthAgo';
  const YEAR_AGO    = 'YearAgo';
  const MINUTE  = 60;
  const HOUR    = 3600;
  const DAY     = 86400;
  
  private $_value = null;
  private $_text = '';
  
  public function timeAgo($date, $key = 'timeAgo')
  {
    $this->_value = null;
    $this->_setTextAndValue($date);
    $translate = new Custom_Translate();
    
    if ($this->_value !== null)
    {
      return $translate->pluralTranslate($key.$this->_text, $this->_value, null, 'general');
    }
    else
    {
      return $translate->translate($key.$this->_text, null, 'general');
    }
  }
  
  private function _setTextAndValue($date)
  {
    $date = new Zend_Date($date, 'YYYY-MM-dd HH:mm:ss');
    $diff = Zend_Date::now()->sub($date)->toValue();
    $diffMinute = floor($diff / self::MINUTE);
    
    if ($diffMinute == 0)
    {
      $this->_text = self::MOMENT_AGO;
    }
    elseif ($diffMinute < 60)
    {
      $this->_text = self::MINUTE_AGO;
      $this->_value = $diffMinute;
    }
    else
    {
      $diffHour = floor($diff / self::HOUR);
      
      if ($diffHour < 24)
      {
        $this->_text = self::HOUR_AGO;
        $this->_value = $diffHour;
      }
      else
      {
        $diffDay = floor($diff / self::DAY);
        if ($diffDay < 7)
        {
          $this->_text = self::DAY_AGO;
          $this->_value = $diffDay;
        }
        else
        {
          $diffWeek = floor($diff / (7 * self::DAY));
          if ($diffWeek < 4)
          {
            $this->_text = self::WEEK_AGO;
            $this->_value = $diffWeek;
          }
          else
          {
            //$day = $date->get('dd');
            //$dayNow = $nowDate->get('dd');
            $nowDate = new Zend_Date(Zend_Date::now(), 'YYYY-MM-dd HH:mm:ss');

            $month = (int)$date->get('MM');
            $monthNow = (int)$nowDate->get('MM');
            $diffMonth = 0;

            $year = (int)$date->get('yyyy');
            $yearNow = (int)$nowDate->get('yyyy');
            $diffYear = $yearNow - $year;

            if($diffYear == 0)
            {
              $diffMonth = $monthNow - $month;
            }
            elseif ($diffYear == 1)
            {
              $diffMonth = $monthNow + (12 - $month);
            }
            
            if ($diffYear < 2 && $diffMonth < 12)
            {
              $this->_text = self::MONTH_AGO;
              $this->_value = $diffMonth;
            }
            else
            {
              $this->_text = self::YEAR_AGO;
              $this->_value = $diffYear;
            }
          }
        }
      }
    }
  }
}