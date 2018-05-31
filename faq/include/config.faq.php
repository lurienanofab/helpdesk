<?php
/* *************************************************************************
  Id: config.faq.php

  Contains some user editable configs at the top of this file.
  You will only need to change them if youre having trouble.
  Also See: faq/include/language/[your selected language]/_localization.php
  Dont change anything at the bottom of this file.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

define('FAQ_VERSION','1.0 ST');//don't change this. Changes from version to version.
define('FAQ_CK_VERSION', '3_4_1');//ckeditor path constant.

/* In most cases these do not require changing.
 * If your using shared SSL you will need to set the 2 xxxx_SERVER variables accordingly.
 */
define('SERVER_DOMAIN', getenv('HTTP_HOST'));// eg: yourdomain.com OR www.yourdomain.com OR yoursubdomain.yourdomain.com ETC.
define('HTTP_SERVER', 'http://' . SERVER_DOMAIN);// non-secure public address. eg: http://
define('HTTPS_SERVER', 'https://' . SERVER_DOMAIN);// secure public address. eg: https://

/* If your having trouble with paths, edit this to match your filesystem public DOCUMENT_ROOT page.
 * Thats the absolute filepath to the folder equalling your website root '/'
 */
//DEFAULT: OSF_DOC_ROOT = $osfConf_DocRootDir
define('OSF_DOC_ROOT', $osfConf_DocRootDir);
//DEFAULT: DIR_FS_WEB_ROOT = $osfConf_WRDir
define('DIR_FS_WEB_ROOT', $osfConf_WRDir);// absolute web file-system path

define('OSF_PHP_SELF', $osfConf_PHP_SELF);

//omit the "DIR_PATH_ADMIN" folder. relative web path to root
define('DIR_WS_REL_ROOT', "./");

define('DIR_WS_ADMIN', DIR_FS_WEB_ROOT . DIR_PATH_ADMIN);// absolute web path of admin dir

// faq upload directories
define('DIR_WS_DOC', DIR_FS_WEB_ROOT . 'faq/pdf/');
define('DIR_WS_IMAGES', DIR_FS_WEB_ROOT . 'faq/images/');

define('DIR_FS_DOC', OSF_DOC_ROOT . DIR_WS_DOC);// absolute file-system path
define('DIR_FS_IMAGES', OSF_DOC_ROOT . DIR_WS_IMAGES);// absolute file-system path

// used internally
define('DIR_WS_IMG', DIR_FS_WEB_ROOT . 'faq/img/');
define('DIR_WS_ICONS', DIR_FS_WEB_ROOT . 'faq/img/icons/');
define('DIR_WS_BUTTONS', DIR_FS_WEB_ROOT . 'faq/img/buttons/');

define('DIR_FAQ_INCLUDES', DIR_WS_REL_ROOT . 'faq/include/');// MUST be relative
define('DIR_FAQ_LANG', DIR_FAQ_INCLUDES . 'language/');


/// Table names
define('TABLE_FAQCATS', TABLE_PREFIX . 'faqcategories');
define('TABLE_FAQS', TABLE_PREFIX . 'faqs');
define('TABLE_FAQS2FAQCATS', TABLE_PREFIX . 'faqs_to_faqcategories');
define('TABLE_FAQ_SETTINGS', TABLE_PREFIX . 'faq_settings');
define('TABLE_FAQ_SETTINGS_LANG', TABLE_PREFIX . 'faq_settings_lang');
define('TABLE_FAQ_ADMIN', TABLE_PREFIX . 'faq_admin');

// Client file names
define('FILE_FAQ', 'faq.php');
define('FILE_FAQ_SUBMIT', 'faq_submit.php');
define('FILE_FAQ_FEED', 'faq_feed.php');
?>