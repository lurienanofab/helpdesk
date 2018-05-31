<?php
/* *************************************************************************
  Id: faq_settings.lang.php

  Language file for staff/faq_settings.inc.php


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

define('OSF_FS_ADDED', 'Added');
define('OSF_FS_DESCRIPTION', 'Description');
define('OSF_FS_KEY', 'Key');
define('OSF_FS_MODIFIED', 'Modified');
define('OSF_FS_SETTING', 'Setting');
define('OSF_FS_UPDATED', 'Value Updated');
define('OSF_FS_VALUE', 'Value');

define('OSF_LANG_NOT_FOUND', 'Language SQL not found in %s');
define('OSF_LANG_ROW_MISMATCH', 'Wrong number of rows in SQL file.<br />I was expecting %s rows but found %s rows.');
define('OSF_LANG_MISMATCH', 'The Language in the SQL language file does not match the selected language name.<br />The system will fallback to english for now. Once the SQL is corrected, try again.');
define('OSF_LANG_SQL_ERROR', 'Errors encountered while processing the database schema file.<br />Please check your logs for errors. Once corrected, try again.<br />Also try using phpMyAdmin to test your SQL in a test database first.');
define('OSF_LANG_UPDATED', 'Database translations updated.');
define('OSF_LANG_SCHEMA_BAD', 'The schema file does not appear to be properly formatted.');
define('OSF_LANG_SCHEMA_EMPTY', 'The schema file is empty or improperly formatted.');
define('OSF_LANG_MISSING', 'Missing key');
define('OSF_LANG_DUPLICATE', 'Duplicated key');
define('OSF_LANG_SCHEMA_IILEGAL', 'The schema file is trying to use sql that is not allowed. Only %s and %s queries are allowed.');

define('OSF_FS_HTACCESS_INFO', 'update .htaccess file?<br />
	<br />
	<b>NOTE:</b> Updating the .htaccess file will overwrite any existing entries in the file at "%s" and enable the SEO URL system if it is turned on (above).<br />
	<br />
	For more information, see the <a href="http://osfaq.oz-devworx.com.au/install.php" target="_blank">installation instructions</a>');

define('OSF_FS_HTACCESS_NOWRITE', '.htaccess file NOT written to %s');
define('OSF_FS_HTACCESS_WRITE', '.htaccess file written to (%s).<br />Please change the file\'s permissions to not writeable.');
define('OSF_FS_HTACCESS_NOT_EXIST', '.htaccess file does NOT exist. Please create it at: "%s" and set its permissions to writable.');
define('OSF_FS_HTACCESS_NOT_WRITEABLE', '.htaccess file is NOT writable. Please change its permissions to writable: "%s"');

define('OSF_FS_SSL_NOT_INSTALLED', '<b>NOTE:</b> SSL is not installed on this server so this setting has been disabled for safety reasons.');
define('OSF_FS_SSL_INSTALLED', 'SSL seems to be installed and working on this server.');

define('OSF_FS_LANGUAGE', 'Language: ');
define('OSF_FS_TIMEZONE', 'Timezone: ');
?>