<?php
/* *************************************************************************
  Id: faq_settings.lang.php

  Language file for staff/faq_settings.inc.php


  German Translation provided by Silvio Paschke 2010

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

define('OSF_FS_ADDED', 'Hinzugefügt');
define('OSF_FS_DESCRIPTION', 'Beschreibung');
define('OSF_FS_KEY', 'Schlüssel');
define('OSF_FS_MODIFIED', 'Geändert');
define('OSF_FS_SETTING', 'Einstellung');
define('OSF_FS_UPDATED', 'Eintrag wurde aktualisiert');
define('OSF_FS_VALUE', 'Wert');

define('OSF_LANG_NOT_FOUND', 'Sprache in SQL-Datei nicht gefunden in %s');
define('OSF_LANG_ROW_MISMATCH', 'Falsche Anzahl der Zeilen in SQL-Datei.<br />Ich hatte erwartet, %s Reihen fand aber %s Zeilen.');
define('OSF_LANG_MISMATCH', 'Die Sprache in der Sprache-SQL-Datei entspricht nicht dem gewählten Sprachnamen.<br />Das System wird jetzt auf Englisch umschalten. Nachdem das SQL-Problem behoben ist, versuchen Sie es erneut.');
define('OSF_LANG_SQL_ERROR', 'Fehler beim Bearbeiten der Datenbank-Schema-Datei.<br />Bitte überprüfen Sie Ihre Protokolle auf Fehler. Sobald dies korrigiert wurde, bitte erneut versuchen.<br />Auch können Sie dies mit phpMyAdmin in einer Testdatenbank zum Test versuchen.');
define('OSF_LANG_UPDATED', 'Datenbankübersetzungen aktualisiert.');
define('OSF_LANG_SCHEMA_BAD', 'Die Schema-Datei scheint nicht richtig formatiert.');
define('OSF_LANG_SCHEMA_EMPTY', 'Die Schema-Datei ist leer oder falsch formatiert.');
define('OSF_LANG_MISSING', 'Fehlende Schlüssel');
define('OSF_LANG_DUPLICATE', 'Doppelelte Schlüssel');
define('OSF_LANG_SCHEMA_IILEGAL', 'Die Schema-Datei versucht SQL-Aktionen auszuführen, die nicht erlaubt sind. Nur %s und %s Abfragen sind zulässig.');

define('OSF_FS_HTACCESS_INFO', '.htaccess Datei aktualisieren?<br />
	<br />
	<b>HINWEIS:</b> Bei Aktualisierung der .htaccess Datei werden alle vorhandenen Einträge in der Datei "%s" überschrieben und das SEO-URL-System aktiviert, wenn es auch aktiviert wurde (oben).<br />
	<br />
	Weitere Informationen finden Sie in der <a href="http://osfaq.oz-devworx.com.au/install.php" target="_blank">Installationsanleitung</a>');

define('OSF_FS_HTACCESS_NOWRITE', '.htaccess Datei konnte NICHT in %s geschrieben werden');
define('OSF_FS_HTACCESS_WRITE', '.htaccess Datei konnte unter (%s) geschrieben werden.<br />Bitte ändern Sie die Berechtigungen für diese Datei auf "nicht beschreibbar".');
define('OSF_FS_HTACCESS_NOT_EXIST', '.htaccess Datei existiert nicht. Bitte erstellen Sie diese unter "%s" und ändern die Berechtigungen dieser Datei auf "beschreibbar".');
define('OSF_FS_HTACCESS_NOT_WRITEABLE', '.htaccess Datei ist nicht beschreibbar. Bitte ändern Sie die Berechtigungen auf "beschreibbar": "%s"');

define('OSF_FS_SSL_NOT_INSTALLED', '<b>HINWEIS:</b> SSL ist nicht auf diesem Server installiert, daher wurde diese Einstellung aus Sicherheitsgründen deaktiviert.');
define('OSF_FS_SSL_INSTALLED', 'SSL scheint installiert und arbeitet auf diesem Server korrekt.');

define('OSF_FS_LANGUAGE', 'Sprache: ');
define('OSF_FS_TIMEZONE', 'Zeitzone: ');
?>