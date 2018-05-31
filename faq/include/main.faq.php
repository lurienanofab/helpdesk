<?php
/* *************************************************************************
  Id: main.faq.php

  Contains required setup functionality and vars for the osFaq system.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

// this is the only constant in this file
define('DIR_PATH_ADMIN', 'scp/');// your admin folder (with trailing /)

// localise calls to parent vars for reduced maintenance & increased flexibility
$osf_isClient = ($thisclient && $thisclient->getId() && $thisclient->isValid());


//gzdecode is only available in PHP >= 6.0.0
if(!function_exists('gzdecode')){
	function gzdecode($string) {
		$string = substr($string, 10);//strip out gzip encode header
		return gzinflate($string);
	}
}

/**
 * Clean a path string to suit this installer.
 * Finds the root path of a given file path.
 *
 * @param string $pathString a path to find the root of
 * @return A path suitable for this installer to use directly
 */
function osfMain_cleanPath($pathString){
  //change windows seperators
  if(false !== strpos($pathString, "\\")){
    $pathString = preg_replace('/(\\\\+)/', '/', $pathString);
  }

  if(substr($pathString, -1)!='/') $pathString .= '/';

  if(substr($pathString, -strlen(DIR_PATH_ADMIN)) == DIR_PATH_ADMIN){
    $pathString = substr($pathString, 0, -strlen(DIR_PATH_ADMIN));
  }
  return $pathString;
}


// get the current page being viewed.
$osfConf_PHP_SELF = getenv('PHP_SELF');
if(empty($osfConf_PHP_SELF)){
  $osfConf_PHP_SELF = getenv('SCRIPT_NAME');
  if(empty($osfConf_PHP_SELF)){
    $osfConf_PHP_SELF = getenv('ORIG_SCRIPT_NAME');
  }
}

// this would indicate that $osfConf_PHP_SELF couldnt be set.
if(false === ($osfConf_rootDir = dirname(realpath(basename($osfConf_PHP_SELF))))){
  exit('FATAL ERROR 1: PHP_SELF not determined correctly: ' . $osfConf_PHP_SELF . '. Please contact the developers.');
}

// root path to the current parent-documents dir
$osfConf_DocRootDir = osfMain_cleanPath($osfConf_rootDir);

// public file system. relative to document root
$osfConf_WRDir = osfMain_cleanPath(dirname($osfConf_PHP_SELF));

// the servers DOCUMENT_ROOT file system path. We calculate this
if(substr($osfConf_DocRootDir, -strlen($osfConf_WRDir)) == $osfConf_WRDir){
  $osfConf_DocRootDir = substr($osfConf_DocRootDir, 0, -strlen($osfConf_WRDir));
}


////DEBUG: debugging stuff. Uncomment to enable.
//echo '<pre>';
//echo '$osfConf_rootDir='.$osfConf_rootDir.PHP_EOL;
//echo '$osfConf_WRDir='.$osfConf_WRDir.PHP_EOL;
//echo '$osfConf_DocRootDir='.$osfConf_DocRootDir.PHP_EOL;
//echo '</pre>';
//exit();


if(defined('_OSFAQ_INSTALL_ACTIVE_')){
  if(substr($osfConf_WRDir, -strlen('faq/setup/')) == 'faq/setup/'){
    $osfConf_WRDir = substr($osfConf_WRDir, 0, -strlen('faq/setup/'));
  }
}


require('config.faq.php');
//cleanup temp stuff
unset($osfConf_rootDir,$osfConf_DocRootDir,$osfConf_WRDir,$osfConf_PHP_SELF);

if(!defined('_OSFAQ_INSTALL_ACTIVE_')){
  /// these config constants come from the database. Allows settings to be editted on the fly.
  $faqConfigQuery = db_query('select key_name, key_value, field_type from '.TABLE_FAQ_SETTINGS.';');
  while ($faqConfig = db_fetch_array($faqConfigQuery)) {
    if(!defined($faqConfig['key_name']) && $faqConfig['field_type']!='heading'){
      define($faqConfig['key_name'], $faqConfig['key_value']);
    }
  }


  // get all languages
  // TODO: add an optional language dropdown box to client side
  // only applies once the faq data has a translation structure.
  if(!isset($_SESSION['osf_languages']) || empty($_SESSION['osf_languages'])){

    // clean up any garbage first.
    if(isset($_SESSION['osf_languages'])){
      unset($_SESSION['osf_languages']);
    }

    $_SESSION['osf_languages'] = array();
    $osf_lang_dir = dir(DIR_FAQ_LANG);

    while (false !== ($osf_lang_file = $osf_lang_dir->read())) {
      if ( (substr($osf_lang_file, 0, 1)!='.') && (substr($osf_lang_file, 0, 1)!='_') && is_dir(DIR_FAQ_LANG . $osf_lang_file) ){
        $_SESSION['osf_languages'][] = array('id' => $osf_lang_file, 'text' => $osf_lang_file);
      }
    }
  }
  //echo '<pre>' . DIR_FAQ_LANG . "\n";print_r($_SESSION['languages']);echo '</pre>';


  require_once(DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/_localization.php');

  // if OSF_TZ is set in the active _localisation file it will be used.
  // Otherwise the value set in osFaq-admin-settings will be used.
  date_default_timezone_set((OSF_TZ=='') ? OSFDB_TIMEZONE : OSF_TZ);
  $osf_langDirection = '<style type="text/css" media="all">body{direction:' . OSF_LANG_DIRECTION . '; unicode-bidi:embed;}</style>' . PHP_EOL;
}
?>