<?php
/* *************************************************************************
  Id: faq_admin.lang.php

  Language file for faq_admin.php
  These constants are shared between more than one page


  German Translation provided by Silvio Paschke 2010

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */
// SHARED MAIN HEADINGS
define('OSF_PAGE_FAQ', 'FAQ Administration');
define('OSF_PAGE_FAQ_SETTINGS', 'FAQ Einstellungen');
define('OSF_PAGE_FAQ_UPLOADS', 'FAQ Upload Manager');
define('OSF_PAGE_FAQ_SITEMAP', 'xml Sitemap');

// SHARED TOOLTIPS/BUTTON TEXT
define('OSF_TIP_CANCEL', 'Abbrechen');
define('OSF_TIP_EDIT', 'Bearbeiten');
define('OSF_TIP_INSERT', 'Bestätigen');
define('OSF_TIP_INFO', 'Info');
define('OSF_TIP_BACK', 'Zurück');
define('OSF_TIP_UPDATE', 'Aktualisieren');

// SHARED TEXT
define('OSF_TEXT_TOP', 'Hauptkategorie');
define('OSF_Q', 'F');
define('OSF_A', 'A');
define('OSF_EXISTS', 'existiert');
define('OSF_WRITABLE', 'beschreibbar');
define('OSF_NOT_WRITABLE', 'nicht beschreibbar');
define('OSF_NOT_EXIST', 'existiert nicht');
define('OSF_OF', ' von ');
define('OSF_CLIENT_DISABLED', 'Die Klient-Seite des FAQ-Bereichs ist gerade nicht online.');
define('OSF_ERROR_FILE_NOT_SAVED', 'FEHLER: Konnte Datei nicht sichern');
define('OSF_BYTES_WRITTEN', ' geschriebene Bytes.');

// faq_admin.php ONLY
define('OSF_BACK_TO_OST', 'Zurück zur Ticket-Administration');
define('OSF_WARN_DOC_DIR_WRITE', 'Das FAQ Dokument-Upload-Verzeichnis ist nicht beschreibbar. <br> Bitte machen Sie es beschreibbar: ' . DIR_FS_DOC);
define('OSF_WARN_DOC_DIR_EXIST', 'Das FAQ Dokument-Upload-Verzeichnis existiert nicht.<br>Bitte erstellen Sie es unter: ' . DIR_FS_DOC);
define('OSF_WARN_IMG_DIR_WRITE', 'Das FAQ Bilder-Upload-Verzeichnis ist nicht beschreibbar. <br> Bitte machen Sie es beschreibbar: ' . DIR_FS_IMAGES);
define('OSF_WARN_IMG_DIR_EXIST', 'Das FAQ Bilder-Upload-Verzeichnis existiert nicht.<br>Bitte erstellen Sie es unter: ' . DIR_FS_IMAGES);
define('OSF_WARN_SETUP_DIR', 'Bitte löschen Sie aus Sicherheitsgründen das osFaq Setup-Verzeichnis.<br /><code>' . OSF_DOC_ROOT . DIR_FS_WEB_ROOT . 'faq/setup/</code>');
define('OSF_WARN_DB_VERSION', 'Die Datei-Version (%s) stimmt nicht mit der Datenbank-Version (%s) überein. Bitte stellen Sie sicher, dass die richtigen Dateien hochgeladen haben und führen den Installationsassistenten unter folgender URL aus:<br /><code>' . OSF_DOC_ROOT . DIR_FS_WEB_ROOT . 'faq/setup/</code>');


require_once('faq_paginator.lang.php');
?>