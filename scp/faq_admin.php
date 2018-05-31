<?php
/* *************************************************************************
 Id: faq_admin.php

 Core FAQ administration code.
 Displayable content is contained in the directory: /faq/include/staff/


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

require ('../faq/include/accelerator.faq.php'); // page accelerator. MUST BE FIRST
define('DIR_WS_REL_ROOT', "../");

/// CONFIGS
require ('staff.inc.php'); // osTicket file
require (ROOT_PATH . 'faq/include/main.faq.php'); // !important

/// DEFAULT LANGUAGE FILE.
require_once (DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/faq_admin.lang.php');

require (DIR_FAQ_INCLUDES . 'FaqFuncs.php');
require (DIR_FAQ_INCLUDES . 'FaqMessage.php');
require (DIR_FAQ_INCLUDES . 'FaqForm.php');

require_once (DIR_FAQ_INCLUDES . 'FaqArrayData.php');
require_once (DIR_FAQ_INCLUDES . 'FaqTable.php');
require_once (DIR_FAQ_INCLUDES . 'FaqSQLExt.php');


/// ADMIN FILES
define('FILE_FAQ_ADMIN', 'faq_admin.php'); //this file (master)
define('FILE_FAQ_ADMIN_WORKER', 'faq_admin_worker.inc.php');
define('FILE_FAQ_ADMIN_INC', 'faq_admin_ui.inc.php');
define('FILE_FAQ_UNUSED_INC', 'faq_upload_man.inc.php');
define('FILE_FAQ_SETTINGS', 'faq_settings.inc.php');
define('FILE_FAQ_MAPPER', 'faq_map_ui.inc.php');
define('FILE_FAQ_MAP_BUILDER', 'faq_map_builder.inc.php');
define('FILE_FAQ_NOT_AUTHORISED', 'faq_not_authorised.inc.php');


/// TABLE ICON IMAGES
define('IMG_ICON_FOLDER', DIR_WS_ICONS . 'folder.png');
define('IMG_ICON_PREVIEW', DIR_WS_ICONS . 'preview.png');

define('IMG_ICON_INFO', DIR_WS_ICONS . 'info.png');
define('IMG_ICON_ARROW_RIGHT', DIR_WS_ICONS . 'edit.png');

define('IMG_ICON_GREEN_ACTIVE', DIR_WS_ICONS . 'status_green_on.gif');
define('IMG_ICON_GREEN_DOWN', DIR_WS_ICONS . 'status_green_off.gif');
define('IMG_ICON_RED_ACTIVE', DIR_WS_ICONS . 'status_red_on.gif');
define('IMG_ICON_RED_DOWN', DIR_WS_ICONS . 'status_red_off.gif');

// localise calls to parent vars for reduced maintenance & increased flexibility
$osf_isAdmin = $thisuser->isAdmin();

// prep some classes
$messageHandler = new FaqMessage;
$faqForm = new FaqForm;
$sqle = new FaqSQLExt;


/// important system warnings
// check if the docs directory exists and is writable
if (is_dir(DIR_FS_DOC)) {
  if (!is_writeable(DIR_FS_DOC))
  $messageHandler->add(OSF_WARN_DOC_DIR_WRITE, FaqMessage::$error);
} else {
  $messageHandler->add(OSF_WARN_DOC_DIR_EXIST, FaqMessage::$error);
}
// check if the images directory exists and is writable
if (is_dir(DIR_FS_IMAGES)) {
  if (!is_writeable(DIR_FS_IMAGES))
  $messageHandler->add(OSF_WARN_IMG_DIR_WRITE, FaqMessage::$error);
} else {
  $messageHandler->add(OSF_WARN_IMG_DIR_EXIST, FaqMessage::$error);
}
// check if the setup directory exists and nag if it does
if (is_dir(OSF_DOC_ROOT . DIR_FS_WEB_ROOT . 'faq/setup/')) {
  $messageHandler->add(OSF_WARN_SETUP_DIR, FaqMessage::$warning);
}
// make sure the file and database versions match
if(!isset($_SESSION['DB_FAQ_VERSION']) || $_SESSION['DB_FAQ_VERSION']!=FAQ_VERSION){
  $result = db_query("SELECT key_value FROM " . TABLE_FAQ_ADMIN . " WHERE key_name LIKE 'DB_FAQ_VERSION';");
  if ($temp_data = db_fetch_array($result)) {
    $_SESSION['DB_FAQ_VERSION']=$temp_data['key_value'];
    if($_SESSION['DB_FAQ_VERSION']!=FAQ_VERSION){
      $messageHandler->add(sprintf(OSF_WARN_DB_VERSION, FAQ_VERSION, $_SESSION['DB_FAQ_VERSION']), FaqMessage::$error);
    }
  }
}
// make sure admin knows when the client side is offline
if(OSFDB_DISABLE_CLIENT=='true'){
  $messageHandler->add(OSF_CLIENT_DISABLED, FaqMessage::$warning);
}

// build the page output
$nav->setTabActive('faq');

if (OSFDB_STAFF_AS_ADMIN=='true' || $osf_isAdmin) {
  /// PAGE OUTPUT
  if (isset($_GET['uploads']) && $_GET['uploads'] == 'true') {
    $inc = FILE_FAQ_UNUSED_INC;
  } elseif (isset($_GET['settings']) && $_GET['settings'] == 'true') {
    $inc = FILE_FAQ_SETTINGS;
  } elseif (isset($_GET['map']) && $_GET['map'] == 'true') {
    $inc = FILE_FAQ_MAPPER;
  } elseif (isset($_GET['mapbuilder']) && $_GET['mapbuilder'] == 'true') {
    $inc = FILE_FAQ_MAP_BUILDER;
  } else {
    $inc = FILE_FAQ_ADMIN_INC;
  }

  $nav->addSubMenu(array('desc' => OSF_PAGE_FAQ.' (v'.FAQ_VERSION.')', 'href' => FILE_FAQ_ADMIN, 'iconclass' => 'helpTopics'));
  $nav->addSubMenu(array('desc' => OSF_PAGE_FAQ_SITEMAP, 'href' => FILE_FAQ_ADMIN . '?map=true', 'iconclass' => 'syslogs'));
  if($osf_isAdmin){
    $nav->addSubMenu(array('desc' => OSF_PAGE_FAQ_SETTINGS, 'href' => FILE_FAQ_ADMIN . '?settings=true', 'iconclass' => 'preferences'));
    $nav->addSubMenu(array('desc' => OSF_PAGE_FAQ_UPLOADS, 'href' => FILE_FAQ_ADMIN . '?uploads=true', 'iconclass' => 'attachment'));
  }
}else{
  $inc = FILE_FAQ_NOT_AUTHORISED;
  $nav->addSubMenu(array('desc' => OSF_BACK_TO_OST, 'href' => 'index.php', 'iconclass' => 'Ticket'));
}

require_once (STAFFINC_DIR . 'header.inc.php'); // osTicket file
echo $osf_langDirection;
//css fix for IE
echo <<<CSS
<!--[if lte IE 8]>
<style type="text/css" media="screen">
.osf_button{background: #DB8606;}
</style>
<![endif]-->
CSS;
require_once (DIR_FAQ_INCLUDES . 'staff/' . $inc);
require_once (STAFFINC_DIR . 'footer.inc.php'); // osTicket file
?>