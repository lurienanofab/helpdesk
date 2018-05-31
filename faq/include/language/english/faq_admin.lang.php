<?php
/* *************************************************************************
  Id: faq_admin.lang.php

  Language file for faq_admin.php
  These constants are shared between more than one page


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */
// SHARED MAIN HEADINGS
define('OSF_PAGE_FAQ', 'FAQ Admin');
define('OSF_PAGE_FAQ_SETTINGS', 'FAQ Settings');
define('OSF_PAGE_FAQ_UPLOADS', 'FAQ Upload Manager');
define('OSF_PAGE_FAQ_SITEMAP', 'xml Sitemap');

// SHARED TOOLTIPS/BUTTON TEXT
define('OSF_TIP_CANCEL', 'Cancel');
define('OSF_TIP_EDIT', 'Edit');
define('OSF_TIP_INSERT', 'Save');
define('OSF_TIP_INFO', 'Info');
define('OSF_TIP_BACK', 'Back');
define('OSF_TIP_UPDATE', 'Update');

// SHARED TEXT
define('OSF_TEXT_TOP', 'TOP LEVEL');
define('OSF_Q', 'Q');
define('OSF_A', 'A');
define('OSF_EXISTS', 'exists');
define('OSF_WRITABLE', 'writable');
define('OSF_NOT_WRITABLE', 'not writable');
define('OSF_NOT_EXIST', 'does not exist');
define('OSF_OF', ' of ');
define('OSF_CLIENT_DISABLED', 'The Client side FAQ area is offline at the moment.');
define('OSF_ERROR_FILE_NOT_SAVED', 'ERROR: Could not save file!');
define('OSF_BYTES_WRITTEN', ' bytes written.');

// faq_admin.php ONLY
define('OSF_BACK_TO_OST', 'Back to Ticket Administration');
define('OSF_WARN_DOC_DIR_WRITE', 'The FAQs document upload directory is not writable.<br>Please make it writable: ' . realpath(DIR_FS_DOC));
define('OSF_WARN_DOC_DIR_EXIST', 'The FAQs document upload directory does not exist.<br>Please create it at: ' . DIR_FS_DOC);
define('OSF_WARN_IMG_DIR_WRITE', 'The FAQ images upload directory is not writable.<br>Please make it writable: ' . realpath(DIR_FS_IMAGES));
define('OSF_WARN_IMG_DIR_EXIST', 'The FAQ images upload directory does not exist.<br>Please create it at: ' . DIR_FS_IMAGES);
define('OSF_WARN_SETUP_DIR', 'Its important you delete the osFaq <code>setup</code> directory for security reasons.<br /><code>' . realpath(OSF_DOC_ROOT . DIR_FS_WEB_ROOT . 'faq/setup/') . '</code>');
define('OSF_WARN_DB_VERSION', 'The file version (%s) does not match the database version (%s). Please make sure you have uploaded the correct files and run the install wizard at:<br /><code>' . OSF_DOC_ROOT . DIR_FS_WEB_ROOT . 'faq/setup/</code>');


require_once('faq_paginator.lang.php');
?>