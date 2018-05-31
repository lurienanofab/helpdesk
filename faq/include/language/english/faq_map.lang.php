<?php
/* *************************************************************************
  Id: faq_map.lang.php


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

define('OSF_SITEMAP_GENERATOR', 'Sitemap generator');

define('OSF_SITEMAP_RESULTS', OSF_SITEMAP_GENERATOR . ' test run');
define('OSF_WORKING', 'Working... Please wait.');

/// common definitions
define('OSF_SITEMAP_ALT', 'Works with Google&trade;, Yahoo!&reg;, Bing&trade;, Ask.com&trade; and others');
define('OSF_SITEMAP_PING', 'Instantly notify Google&trade;, Yahoo!&reg;, Bing&trade; and Ask.com&trade; on completion?');
define('OSF_SITEMAP_PING_RESULT', 'Search Engines were informed of the new file. Results are below:');

define('OSF_SITEMAP_OUTPUT', 'Output to:');
define('OSF_APPEND_TO', 'Append to: ');

define('OSF_SITEMAP_LOCAL', 'Write a complete sitemap file');
define('OSF_SITEMAP_OTHER', 'Append this sitemap tree to an existing sitemap');
define('OSF_SM_OTHER_OPT1', 'Append elements to an existing <b>sitemap</b> file');
define('OSF_SM_OTHER_OPT2', 'Save the results to a new sitemap file and appends a reference to it in an existing <b>sitemap_index</b> file.');
define('OSF_SITEMAP_TEST', 'Just show me the results without writing to a file');


//big one. This is the help information
define('OSF_SITEMAP_HELP', '
<b class="sm_alt_text">What is an xml Sitemap?</b>
<p>An xml sitemap is a web spidering tool designed to assist search engine indexing robots with indexing your site correctly.<br />
The specifications used by this system can be found at: <a href="http://www.sitemaps.org/protocol.php" target="_blank">http://www.sitemaps.org/protocol.php</a></p>

<b class="sm_alt_text">Important Notes:</b>
<p>If a sitemap file you want to write to is marked as <b>"does not exist"</b>, you will need to create the file on your server first and set its permissions to writable.</p>
<p>If a sitemap file you want to write to is marked as <b>"not writable"</b>, you will need to set its permissions to writable.</p>

<b class="sm_alt_text">Use:</b>
<p>Select your desired <b>"' . OSF_SITEMAP_OUTPUT . '"</b> option.</p>
<p>Each option has different requirements which will be displayed after selecting. Options are:</p>
<ol>
  <li><b>"' . OSF_SITEMAP_LOCAL . '"</b> saves a complete sitemap file to your selected location.</li>
  <li><b>"' . OSF_SITEMAP_OTHER . '"</b> appends the results to an existing sitemap. Options are:</li>
  <ul>
    <li>' . OSF_SM_OTHER_OPT1 . '</li>
    <li>' . OSF_SM_OTHER_OPT2 . '</li>
  </ul>
  <li><b>"' . OSF_SITEMAP_TEST . '"</b> writes its output to a browser window for testing purposes.<br />Nothing is saved to disk.</li>
</ol>
<br />

<b class="sm_alt_text">How to leverage a sitemap.xml file:</b>
<ol>
  <li>Use the instant notification checkbox to notify Google&trade;, Yahoo!&reg;, Bing&trade; and Ask.com&trade; when you generate a new sitemap.</li>
  <li>Add a sitemap entry to your websites <b>"robots.txt"</b> file. See: <a href="http://www.sitemaps.org/protocol.php#informing" target="_blank">http://www.sitemaps.org/protocol.php#informing</a></li>
  <li>Create an account and monitor your sitemaps status at <b>"Google&trade; Webmaster Tools"</b>. See: <a href="https://www.google.com/webmasters/tools/" target="_blank">https://www.google.com/webmasters/tools/</a></li>
  <li>Create an account and monitor your sitemaps status at <b>"Yahoo!&reg; Site Explorer"</b>. See: <a href="http://siteexplorer.search.yahoo.com/" target="_blank">http://siteexplorer.search.yahoo.com/</a></li>
  <li>Create an account and monitor your sitemaps status at <b>"Bing&trade; Webmaster Center"</b>. See: <a href="http://www.bing.com/webmaster" target="_blank">http://www.bing.com/webmaster</a></li>
</ol>

<hr />
<span style="font-size:10px;color:#999999;">Footnotes:<br />
Google&trade; is a registered trademark of Google.<br />
Yahoo!&reg; is a registered trademark of Yahoo<br />
Bing&trade; is a registered trademark of Microsoft&trade;<br />
Ask.com&trade; is a registered trademark of Ask.com.</span>
');


define('OSF_DESCRIPTION', 'Description:');
define('OSF_SM_DESCRIPTION', 'Last Sitemap Description');

define('OSF_NEW_DESCRIPTION', 'The file must exist and have full write permissions. Each file in the list below will indicate its current state.
<br />This option will overwrite the selected file when the sitemap is generated.');


define('OSF_APD_DESCRIPTION', 'This option will append your sitemap elements to an existing sitemap.
<br />If you choose to append to a sitemap_index file, a new sitemap will be written and a reference to it appended to your existing sitemap_index file.
<br />Only valid sitemaps and sitemap_index files are listed. This check is based on the entries already inside the files. Empty files are not considered valid.');


define('OSF_TEST_DESCRIPTION', 'This option will not write or modify any files. The output will be displayed in a browser window.
<br />Its recommended you run this option first if you have not run the sitemapper before. The output will make it easy to see if you need to create any files on your server first.');


define('OSF_INDEX_TEST', 'This is the last sitemap in a set of %d.<br />
The sitemaps in this set use gzip compression but are displayed in plaintext so its readable here.<br />
This set also has a sitemap_index file.<br />
The sitemap_index will contain a list of URLs for all sitemaps in this set and is displayed here below the last sitemap.<br />
The stats below are indicative of generating all files and the index in this set.
<br /><br />
Sitemap URLs will use absolute URLs in the actual files. If any files don\'t exist, you will be advised to create them in the index file output below.<br />');


define('OSF_MAP_TEST', 'The stats below are indicative of generating the actual file.');

define('OSF_APD_TITLE', 'Append Options');
define('OSF_NEW_TITLE', 'Write Options');
define('OSF_MAPUI_TITLE', 'xml Sitemaps help');
define('OSF_APD_NO_SITEMAP', 'No valid sitemap files found on this domain.');
define('OSF_APD_NO_INDEX', 'No valid sitemap_index files found on this domain.');
define('OSF_STATISTICS', 'Statistics:');
define('OSF_ESTIMATED_RUNTIME', 'Estimated Runtime: %s seconds');
define('OSF_MEMORY_USAGE', 'Memory Usage: ');
define('OSF_URLS_WRITTEN', ' urls written.');

define('OSF_SITEMAP_DEST', 'Sitemap destination');
define('OSF_SITEMAP_URL', 'Sitemap URL:');
define('OSF_SITEMAP_CREATE', 'Generate Sitemap');
define('OSF_SITEMAP_WRITE', 'Last Write:');
define('OSF_SM_ENTRIES', 'Sitemap Entries:');
define('OSF_SM_LOCATION', 'Location:');
define('OSF_SM_OUTPUT', 'Sitemap Ouput:');


define('OSF_SITEMAP_SUCCESS', 'Sitemap written to "%s" successfully!');
define('OSF_SITEMAP_SUCCESS_GZ', 'Sitemap entries written to "%s" successfully!');
define('OSF_SITEMAP_SUCCESS_IDX', 'Split-Sitemaps compressed and saved. The sitemap_index file was written to "%s" successfully!');

define('OSF_SITEMAP_ERROR_NO_SM', '%s does not exist. Please create the file and set its permissions to 777.');
define('OSF_SITEMAP_ERROR_RESTRICTED', '%s is not writable. Please change the files permissions to 777.');
define('OSF_SITEMAP_ERROR', 'ERROR: A problem was encountered writting to "%s".');
define('OSF_SITEMAP_ERROR_OUTPUT', 'Somethings gone wrong? Sorry about that. If this error persists please make a bug report via: <a href="http://sourceforge.net/projects/osfaq/support" target="_blank">http://sourceforge.net/projects/osfaq/support</a>');
?>