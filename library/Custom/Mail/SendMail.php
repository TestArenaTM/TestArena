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
class Custom_Mail_SendMail
{
  public static function sendmail($subject, $body, $emailTo = false, $nameTo = false, $emailFrom = false, $nameFrom = false, $altBody = '', $addFoot = true)
  {
    try
    {
      $dir = Zend_Registry :: get('config')->mail->phpmailerPath;

      Zend_Loader::loadFile('class.phpmailer.php', $dir, true);

      $mail             = new PHPMailer(true);
      $mail->CharSet    = Zend_Registry :: get('config')->mail->charset;

      if ( Zend_Registry :: get('config')->mail->smtp->is_smtp )
      {
        $mail->IsSMTP();
        $mail->SMTPAuth      = Zend_Registry :: get('config')->mail->smtp->is_smtp;     // enable SMTP authentication
        $mail->SMTPSecure    = Zend_Registry :: get('config')->mail->smtp->SMTPSecure;  // sets the prefix to the servier
        $mail->Host          = Zend_Registry :: get('config')->mail->smtp->Host;        // sets GMAIL as the SMTP server

        if( Zend_Registry :: get('config')->mail->smtp->Port )
        {
          $mail->Port      = Zend_Registry :: get('config')->mail->smtp->Port;        // set the SMTP port for the GMAIL server
        }

        $mail->Sender        = Zend_Registry :: get('config')->mail->smtp->Sender;

        $mail->Username      = Zend_Registry :: get('config')->mail->smtp->Username;    // GMAIL username
        $mail->Password      = Zend_Registry :: get('config')->mail->smtp->Password;    // GMAIL password
      }

      $mail->From       = ($emailFrom) ? $emailFrom : Zend_Registry :: get('config')->mail->emailFrom;
      $mail->FromName   = self::_convEncoding((($nameFrom) ? $nameFrom : Zend_Registry :: get('config')->mail->nameFrom), $mail->CharSet);
      $emailTo          = ($emailTo) ? $emailTo : Zend_Registry :: get('config')->mail->emailTo;
      $nameTo           = self::_convEncoding((($nameTo) ? $nameTo : Zend_Registry :: get('config')->mail->nameTo), $mail->CharSet);
      $mail->Subject    = self::_convEncoding($subject, $mail->CharSet);

      if( !$body && $altBody )
      {
        $mail->IsHTML(false);
        $mail->Body = self::_convEncoding($altBody, $mail->CharSet);
      }
      else
      {
        $body = str_replace("[\]",'',$body);

        $mail->IsHTML(true);
        $mail->Body = self::_convEncoding($body, $mail->CharSet);

        if($altBody)
        {
          $mail->AltBody = self::_convEncoding($altBody, $mail->CharSet);
        }
        else
        {
          $altBody = str_replace("\r","",str_replace("\n","",$body));
          $altBody = strip_tags(preg_replace('/<br[^>]*>/i', "\n", $altBody));
          $mail->AltBody = self::_convEncoding($altBody, $mail->CharSet);
        }
      }

      $mail->AddAddress($emailTo, $nameTo);
    
      return $mail->Send();
    }
    catch (phpmailerException $e)
    {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }

  private static function _convEncoding($str, $out = 'ISO-8859-2', $in = 'UTF-8')
  {
    if ( strcasecmp($out, $in) == 0 )
    {
      return $str;
    }
    
    return iconv($in, "$out//IGNORE", $str);
  }
}