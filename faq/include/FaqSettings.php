<?php
/* *************************************************************************
  Id: FaqSettings.php

  A collection of support functions for faq_settings.inc.php


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqSettings{

  private function __construct() {

  }



  /**
   * Used for testing if a value is a color
   * and/or to display a color swatch if the value is a color.
   *
   * @param mixed $string
   * @return a color swatch if the value is a 3 or 6 digit hexidecimal color reference; otherwise null is returned
   */
  public static function is_string_a_color($string){
  	return preg_match('@^#[a-f0-9]{6}|#[a-f0-9]{3}$@Ui', $string) ? '<div style="width:50px; height:20px; background-color:' . $string . ';">&nbsp;</div>' : null;
  }

  /**
   * Get a list of timezones supported on this server
   * or fallback to php.ini date.timezone
   * and any manual entries in the active localisation file.
   *
   * @return a 2 dimensional array of timezone names
   */
  public static function getTimezones(){
    $timezone_list = array();
    //php >= 5.1.0
    if(function_exists('timezone_identifiers_list')){
      $timezones = timezone_identifiers_list();
      foreach($timezones as $id => $name){
        $timezone_list[] = array('id' => $name, 'text' => $name);
      }
    }else{
      //php < 5.1.0
      $timezone_list[] = array('id' => getenv('date.timezone'), 'text' => getenv('date.timezone'));
      if(OSF_TZ!='' && !in_array(OSF_TZ, $timezone_list)){
        $timezone_list[] = array('id' => OSF_TZ, 'text' => OSF_TZ);
      }
    }

    return $timezone_list;
  }

  public static function updateHtaccessFile(){
    global $messageHandler;

    $hta_file_name = '../.htaccess';
    $rwbase = DIR_FS_WEB_ROOT;
    $output_data = <<<HTA
Options +FollowSymLinks
RewriteEngine On
RewriteBase $rwbase

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f

RewriteRule ^(.*)-c([0-9]+)-a([0-9]+)-p.html\$ faq.php?cid=\$2&answer=\$3&print=true&%{QUERY_STRING}
RewriteRule ^(.*)-c([0-9]+)-a([0-9]+).html\$ faq.php?cid=\$2&answer=\$3&%{QUERY_STRING}
RewriteRule ^(.*)-c([0-9]+)-a([0-9]+)-(pg|i)([0-9]+)-p.html\$ faq.php?cid=\$2&answer=\$3&\$4=\$5&print=true&%{QUERY_STRING}
RewriteRule ^(.*)-c([0-9]+)-a([0-9]+)-(pg|i)([0-9]+).html\$ faq.php?cid=\$2&answer=\$3&\$4=\$5&%{QUERY_STRING}
RewriteRule ^(.*)-c([0-9]+)-(pg|i)([0-9]+)-p.html\$ faq.php?cid=\$2&\$3=\$4&print=true&%{QUERY_STRING}
RewriteRule ^(.*)-c([0-9]+)-(pg|i)([0-9]+).html\$ faq.php?cid=\$2&\$3=\$4&%{QUERY_STRING}
RewriteRule ^(.*)-c([0-9]+)-p.html\$ faq.php?cid=\$2&print=true&%{QUERY_STRING}
RewriteRule ^(.*)-c([0-9]+).html\$ faq.php?cid=\$2&%{QUERY_STRING}
HTA;

    if(!is_file($hta_file_name)){
      $messageHandler->addNext(sprintf(OSF_FS_HTACCESS_NOT_EXIST, realpath($hta_file_name)), FaqMessage::$warning);
    }elseif(!is_writeable($hta_file_name)){
      $messageHandler->addNext(sprintf(OSF_FS_HTACCESS_NOT_WRITEABLE, realpath($hta_file_name)), FaqMessage::$warning);
    // write to file
    }elseif(false === file_put_contents($hta_file_name, $output_data)){
      $messageHandler->addNext(sprintf(OSF_FS_HTACCESS_NOWRITE, realpath($hta_file_name)), FaqMessage::$error);
    }else{
      $messageHandler->addNext(sprintf(OSF_FS_HTACCESS_WRITE, realpath($hta_file_name)), FaqMessage::$success);
    }
  }
}
?>