<?php
/* *************************************************************************
  Id: faq_upload_man.lang.php

  Language file for staff/faq_upload_man.inc.php


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */


// opening text
define('OSF_ACTIONS', 'Below is a list of cleanup actions that were carried out.');

define('OSF_EXPLAIN', '<p>This page will allow you to remove unused images and documents that you have uploaded over time but are no longer used in your FAQs.</p>
<p>Below is a list of valid files and unused files that will be removed if you continue to the next step.</p>
<p>Please check that no valid files are marked for deletion. If you arent sure, it might be a good idea to backup your existing image and document folders first.</p>
<p>The folders are located at:<br />
<code>' .DIR_FS_IMAGES. '</code><br />
and<br />
<code>' .DIR_FS_DOC. '</code></p>');


// buttons
define('OSF_FINISHED', 'Finished');
define('OSF_AGAIN', 'Check Again');
define('OSF_CANCEL', 'Cancel');
define('OSF_PERFORM', 'Perform Cleanup');

// main headings
define('OSF_IMAGES', 'FAQ Images');
define('OSF_DOCS', 'FAQ Documents');

// minor headings
define('OSF_IMG_VALID', 'VALID IMAGES');
define('OSF_IMG_TO_DEL', 'IMAGES MARKED FOR DELETION');
define('OSF_IMG_VALID_UNALTERED', 'VALID UNALTERED IMAGES');
define('OSF_IMG_DELETED', 'DELETED IMAGES');

define('OSF_DOC_VALID', 'VALID DOCUMENTS');
define('OSF_DOC_TO_DEL', 'DOCUMENTS MARKED FOR DELETION');
define('OSF_DOC_VALID_UNALTERED', 'VALID UNALTERED DOCUMENTS');
define('OSF_DOC_DELETED', 'DELETED DOCUMENTS');

// appended text
define('OSF_DAMAGED_NAME', 'Corrupt file name');
define('OSF_REMOTE_OR_MISSING', 'Remote or missing file');
?>