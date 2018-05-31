<?php
/* *************************************************************************
 Id: _localization.php

 Localization settings for this language.

 README:
 The SQL file in this folder contains database translations for the admin settings.
 It gets inserted into the database automatically when you change to a new language,
 install osFaq or upgrade osFaq.
 If any errors are encountered in the SQL file or if it doesn't exist,
 the system will revert to the older entries (if previously installed)
 or fallback to english.

 SQL NOTES:
 The SQL file should NOT contain any comments.
 Titles and descriptions must NOT contain the ; character.


 German Translation provided by Silvio Paschke 2010

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

//for options see: http://php.net/manual/en/function.date.php
define('OSF_DATE_FMT_STD', 'd.m.Y');
define('OSF_DATE_FMT_MED', 'Y-m-d h:i:s');// international/timestamp
define('OSF_DATE_FMT_LONG', 'j. F Y, G:i');

/* ltr or rtl or ttb.
 * ttb is not recommended due to html formatting of tabs and headings; use rtl instead.
 * see: http://www.w3.org/TR/CSS21/visuren.html#direction
 */
define('OSF_LANG_DIRECTION', 'ltr');

/* Timezone fix for date related functions (PHP >= 5.3.1)
 *
 * The Timezone value set in the osFaq Settings area will be used by default.
 * If you are running php < 5.1.0 and dont have "date.timezone" set
 * in php.ini you will need to manually set the value below
 * using a timezone from this list: http://php.net/manual/en/timezones.php.
 * Otherwise leave the value empty. EG: define('OSF_TZ', '');
 */
define('OSF_TZ', '');
?>