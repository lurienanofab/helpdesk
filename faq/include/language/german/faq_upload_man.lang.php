<?php
/* *************************************************************************
  Id: faq_upload_man.lang.php

  Language file for staff/faq_upload_man.inc.php


  German Translation provided by Silvio Paschke 2010

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */


// opening text
define('OSF_ACTIONS', 'Unterhalb wird die Liste der Aufräumaktionen angezeigt, welche durch das System festgestellt wurden.');

define('OSF_EXPLAIN', '<p>Diese Seite erlaubt Ihnen ungenutzte Bilder und Dokumente, welche zuvor hochgeladen wurden, aber nicht mehr in den FAQs verknüpft sind.</p>
<p>Unterhalb erscheint die Liste der gültigen Dateien und der ungenutzen Dateien, welche im nächsten Schritt gelöscht werden, insofern Sie mit dem nächsten Schritt fortfahren.</p>
<p>Bitte prüfen Sie, dass keine gültigen Dateien für die Löschung markiert wurden. Wenn Sie sich nicht sicher sind, so raten wir Ihnen, zuvor eine Sicherung Ihres Bilder- und Dokumente-Verzeichnisses anzulegen.</p>
<p>Diese finden Sie hier:<br />
<code>' .DIR_FS_IMAGES. '</code><br />
<code>' .DIR_FS_DOC. '</code></p>');


// buttons
define('OSF_FINISHED', 'Vorgang abgeschlossen');
define('OSF_AGAIN', 'Erneut prüfen');
define('OSF_CANCEL', 'Abbrechen');
define('OSF_PERFORM', 'Aufräumaktion durchführen');

// main headings
define('OSF_IMAGES', 'FAQ Bilder');
define('OSF_DOCS', 'FAQ Dokumente');

// minor headings
define('OSF_IMG_VALID', 'GÜLTIGE BILDER');
define('OSF_IMG_TO_DEL', 'ZUR LÖSCHUNG MARKIERTE BILDER');
define('OSF_IMG_VALID_UNALTERED', 'GÜLTIGE, UNVERÄNDERTE BILDER');
define('OSF_IMG_DELETED', 'GELÖSCHTE BILDER');

define('OSF_DOC_VALID', 'GÜLTIGE DOKUMENTE');
define('OSF_DOC_TO_DEL', 'ZUR LÖSCHUNG MARKIERTE DOKUMENTE');
define('OSF_DOC_VALID_UNALTERED', 'GÜLTIGE, UNVERÄNDERTE DOKUMENTE');
define('OSF_DOC_DELETED', 'GELÖSCHTE DOKUMENTE');

// appended text
define('OSF_DAMAGED_NAME', 'Korrupte Dateinamen');
define('OSF_REMOTE_OR_MISSING', 'Ferne (URL) oder fehlende Datei');

?>