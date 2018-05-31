<?php
/* *************************************************************************
  Id: faq_map.lang.php


  German Translation provided by Silvio Paschke 2010

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

define('OSF_SITEMAP_GENERATOR', 'Sitemap-Generator');

define('OSF_SITEMAP_RESULTS', OSF_SITEMAP_GENERATOR . ' Testlauf');
define('OSF_WORKING', 'Arbeite... Bitte warten.');

/// common definitions
define('OSF_SITEMAP_ALT', 'Funktioniert mit Google&trade;, Yahoo!&reg;, Bing&trade;, Ask.com&trade; und anderen');
define('OSF_SITEMAP_PING', 'Sofort nach Fertigstellung an Google&trade;, Yahoo!&reg;, Bing&trade; and Ask.com&trade; melden?');
define('OSF_SITEMAP_PING_RESULT', 'Suchmaschinen wurden über die neue Datei benachrichtigt. Ergebnisse finden Sie unterhalb:');

define('OSF_SITEMAP_OUTPUT', 'Ausgabe:');
define('OSF_APPEND_TO', 'Anfügen an: ');

define('OSF_SITEMAP_LOCAL', 'Schreibe eine vollständige Sitemap-Datei');
define('OSF_SITEMAP_OTHER', 'Füge diesen Sitemap-Baum an eine existierende Sitemap an');
define('OSF_SM_OTHER_OPT1', 'Füge Elemente an eine existierende Sitemap-Datei an');
define('OSF_SM_OTHER_OPT2', 'Speichere die Ergebnisse in eine neue Sitemap-Datei und füge einen Verweis darauf in eine bestehende Sitemap-Datei.');
define('OSF_SITEMAP_TEST', 'Zeige nur die Ergebnisse an ohne eine Datei zu schreiben');


//big one. This is the help information
define('OSF_SITEMAP_HELP', '
<b class="sm_alt_text">Was ist eine xml Sitemap?</b>
<p>Eine xml Sitemap ist ein Web-Spidern-Tool, welches entwickelt wurde, um die Indizierung durch Suchmaschinen-Roboter zwecks Indizierung Ihrer Website korrekt zu unterstützen. <br /> Die Spezifikationen, welche von diesem System verwendet werden, können unter <a href="http://www.sitemaps.org/protocol.php" target="_blank">http://www.sitemaps.org/protocol.php</a> eingesehen werden.</p>

<b class="sm_alt_text">Wichtige Anmerkungen:</b>
<p>Wenn eine Sitemap-Datei, die Sie zu schreiben versuchen, als <b> "existiert nicht" </b> angezeigt wird, müssen Sie die Datei auf Ihrem Server zuerst erstellen und dessen Berechtigungen auf beschreibbar setzen. </p>
<p>Wenn eine Sitemap-Datei, die Sie zu schreiben versuchen, als <b> "nicht beschreibbar" </b> angezeigt wird, müssen Sie die Berechtigungen auf beschreibbar setzen.</p>

<b class="sm_alt_text">Benutze:</b>
<p>Wählen Sie Ihre gewünschte Ausgabeoption aus.</p>
<p>Jede Option hat verschiedene Einstellungen, welche nach Auswahl angezeigt werden. Dies wären:</p>
<ol>
  <li><b>"' . OSF_SITEMAP_LOCAL . '"</b> speichert eine vollständige Sitemap-Datei unter dem ausgewählten Pfad.</li>
  <li><b>"' . OSF_SITEMAP_OTHER . '"</b> fügt die Ergebnisse an eine existierende Sitemap-Datei. Weitere Eistellmöglichkeiten sind:</li>
  <ul>
    <li>' . OSF_SM_OTHER_OPT1 . '</li>
    <li>' . OSF_SM_OTHER_OPT2 . '</li>
  </ul>
  <li><b>"' . OSF_SITEMAP_TEST . '"</b> gibt die Sitemap-Datei im Browser zu Testzwecken auf den Bildschirm aus.<br /> Nichts wird dabei gespeichert.</li>
</ol>
<br />

<b class="sm_alt_text">Wie man eine sitemap.xml Datei wirksam einsetzen kann:</b>
<ol>
  <li>Benutzen Sie die Checkbox für die sofortige Benachrichtigung von Google&trade;, Yahoo!&reg;, Bing&trade; and Ask.com&trade;, wenn eine Sitemap-Datei generiert wird.</li>
  <li>Fügen Sie einen Sitemap-Eintrag zu Ihrer auf der Webseite befindlichen <b>"robots.txt"</b> Datei. Siehe: <a href="http://www.sitemaps.org/protocol.php#informing" target="_blank">http://www.sitemaps.org/protocol.php#informing</a></li>
  <li>Erstellen Sie ein Konto und beobachten Sie Ihren Sitemap-Status via <b>"Google&trade; Webmaster Tools"</b>. Siehe: <a href="https://www.google.com/webmasters/tools/" target="_blank">https://www.google.com/webmasters/tools/</a></li>
  <li>Erstellen Sie ein Konto und beobachten Sie Ihren Sitemap-Status via <b>"Yahoo!&reg; Site Explorer"</b>. Siehe: <a href="http://siteexplorer.search.yahoo.com/" target="_blank">http://siteexplorer.search.yahoo.com/</a></li>
  <li>Erstellen Sie ein Konto und beobachten Sie Ihren Sitemap-Status via <b>"Bing&trade; Webmaster Center"</b>. Siehe: <a href="http://www.bing.com/webmaster" target="_blank">http://www.bing.com/webmaster</a></li>
</ol>

<hr />
<span style="font-size:10px;color:#999999;">Fußnote:<br />
Google&trade; ist eine eingetragene Marke von Google.<br />
Yahoo!&reg; ist eine eingetragene Marke von Yahoo<br />
Bing&trade; ist eine eingetragene Marke von Microsoft&trade;<br />
Ask.com&trade; ist eine eingetragene Marke von Ask.com.</span>
');


define('OSF_DESCRIPTION', 'Beschreibung:');
define('OSF_SM_DESCRIPTION', 'Letzte Sitemap-Beschreibung');

define('OSF_NEW_DESCRIPTION', 'Die Datei muss existieren und volle Schreibrechte haben. Jede Datei unten in der Liste zeigt den aktuellen Status.
<br /> Diese Option wird die ausgewählte Datei, wenn die Sitemap generiert wird, überschreiben.');


define('OSF_APD_DESCRIPTION', 'Diese Option wird Ihre Sitemap-Elemente an eine existierende Sitemap-Datei anhängen.
<br /> Wenn Sie wählen, dass Elemente an eine sitemap_index Datei angehängt werden sollen, wird eine neue Sitemap-Datei geschrieben und ein Verweis darauf angehängt.
<br /> Nur gültig Sitemaps und sitemap_index Dateien werden aufgelistet. Diese Prüfung basiert auf den in der Datei enthaltenen Einträgen. Leere Dateien werden nicht als gültig angesehen.');


define('OSF_TEST_DESCRIPTION', 'Diese Option wird keine Dateien schreiben oder ändern. Die Ausgabe wird in einem Browserfenster angezeigt.
<br /> Diese Option ist empfohlenen, wenn Sie den Sitemapper das erste Mal ausführen. Der Ausgabe erleichtert Ihnen, ob Dateien erstellt werden müssen.');


define('OSF_INDEX_TEST', 'Dies ist die letzte Sitemap in dem Set von %d.<br />Die Sitemaps in diesem Set benutzen die gzip-Kompression, werden aber als einfacher Text und somit lesbar dargestellt.<br />
Dieses Set beinhaltet auch eine sitemap_index Datei.<br />
Der sitemap_index beinhaltet eine Liste der URLs aller Sitemaps diesesn Sets und wird hier unter der letzten Sitemap angezeigt.<br />
Die Statistik unten ist bezeichnend für die Erstellung aller Dateien und den Index in diesem Set.
<br /><br />
Sitemap URLs nutzen absolute URLs in den Dateien. Wenn Dateien nicht vorhanden sind, wird Ihnen geraten, sie bei der Ausgabe der Index-Datei unten zu erstellen.<br />');


define('OSF_MAP_TEST', 'Die Statistik unten ist bezeichnend für die Erstellung der eigentlichen Datei.');

define('OSF_APD_TITLE', 'Anfüge-Optionen');
define('OSF_NEW_TITLE', 'Schreib-Optionen');
define('OSF_MAPUI_TITLE', 'xml Sitemaps Hilfe');
define('OSF_APD_NO_SITEMAP', 'Keine gültigen Sitemap-Dateien für diese Domain gefunden.');
define('OSF_APD_NO_INDEX', 'Keine gültigen sitemap_index Dateien für diese Domain gefunden.');
define('OSF_STATISTICS', 'Statistik:');
define('OSF_ESTIMATED_RUNTIME', 'Voraussichtliche Ausführungszeit: %s Sekunden');
define('OSF_MEMORY_USAGE', 'Speichernutzung: ');
define('OSF_URLS_WRITTEN', ' URLs geschrieben.');

define('OSF_SITEMAP_DEST', 'Sitemap-Ziel');
define('OSF_SITEMAP_URL', 'Sitemap URL:');
define('OSF_SITEMAP_CREATE', 'Generiere Sitemap');
define('OSF_SITEMAP_WRITE', 'Letzter Schreibvorgang:');
define('OSF_SM_ENTRIES', 'Sitemap-Einträge:');
define('OSF_SM_LOCATION', 'Ort:');
define('OSF_SM_OUTPUT', 'Sitemap-Ausgabe:');


define('OSF_SITEMAP_SUCCESS', 'Sitemap erfolgreich unter "%s" gespeichert!');
define('OSF_SITEMAP_SUCCESS_GZ', 'Sitemap-Einträge erfolgreich unter "%s" gespeichert!');
define('OSF_SITEMAP_SUCCESS_IDX', 'Split-Sitemaps komprimiert und gespeichert. Die sitemap_index Datei wurde erfolgreich unter "%s" gespeichert!');

define('OSF_SITEMAP_ERROR_NO_SM', '%s existiert nicht. Bitte erstellen Sie die Datei und setzen die Berechtigungen auf 777.');
define('OSF_SITEMAP_ERROR_RESTRICTED', '%s ist nicht beschreibbar. Bitte ändern Sie die Berechtigungen der Datei auf 777.');
define('OSF_SITEMAP_ERROR', 'Fehler: Ein Problem wurde während des Schreibvorgangs unter "%s" festgestellt.');
define('OSF_SITEMAP_ERROR_OUTPUT', 'Etwas hat nicht korrekt funktioniert? Entschuldigung hierfür. Wenn der Fehler weiterhin besteht, erstellen Sie bitte einen Fehler-Bericht unter: <a href="http://sourceforge.net/projects/osfaq/support" target="_blank">http://sourceforge.net/projects/osfaq/support</a>');
?>