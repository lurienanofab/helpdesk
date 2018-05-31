<?php
/* *************************************************************************
 Id: faq_assist.php

 Handles supplying IFramed pages to the admin area while still
 maintaining security for the supplied files.


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
require (DIR_FAQ_INCLUDES . 'FaqFuncs.php');

if(isset($_GET['img_browse'])){
  //basic image browser (as yet this is not paginated)
  require_once (DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/faq_admin_ui.lang.php');
  require(DIR_FAQ_INCLUDES . 'staff/faq_img_browser.php');
}else{
  //ajax upload script
  require(DIR_FAQ_INCLUDES . 'staff/faq_upload.php');
}
?>