<?php
/* *************************************************************************
  Id: faq.lang.php

  Language file for faq.php


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

define('OSF_TITLE', 'Frequently Asked Questions');
define('OSF_LINK', 'FAQs');
define('OSF_SEARCH_LINK', 'Search for: ');
define('OSF_SEARCH_FIELD', 'search the FAQs');
define('OSF_SEARCH_ANSWER', 'Also search answer text');
define('OSF_SEARCH_BTN', 'Search FAQs');

define('OSF_Q', 'Q');
define('OSF_A', 'A');
define('OSF_CATS', 'Categories');
define('OSF_PARENT_CATS', 'Parent Categories');
define('OSF_SUB_CATS', 'Sub Categories');
define('OSF_SEARCH_RESULTS', 'FAQ: Search Results');
define('OSF_FAQ_NAME', 'Category: ');
define('OSF_FAQ_AUTHOR', 'Author:');

define('OSF_ALL_CATS', 'TOP LEVEL');
define('OSF_TOP_LEVEL', 'TOP LEVEL');
define('OSF_SUGGEST_NEW', 'Suggest a FAQ');

define('OSF_REVEAL_HIDE', 'Click the question to reveal the answer, click the question once more to hide.');
define('OSF_SELECT_CAT', 'Select a category to view the associated FAQs.');

define('OSF_TOP', 'TOP');
define('OSF_BACK', 'Back');

define('OSF_SHOW', 'Show');
define('OSF_HIDE', 'Hide');
define('OSF_ALL_ANSWERS', ' ALL Answers');

define('OSF_NO_FAQS', 'No FAQs to display at the moment.');
define('OSF_CLIENT_DISABLED', 'The FAQ system is offline at the moment. Please try again later.');

define('OSF_ERROR_FILE_NOT_SAVED', 'ERROR: Could not save file!');
define('OSF_BYTES_WRITTEN', ' bytes written.');

define('OSF_PDF_DOWNLOAD', 'Download PDF Datasheet: ');
define('OSF_ADOBE_READER', '<small><br />Please note you will need the free <a href="http://get.adobe.com/reader/" target="_blank">Adobe Reader&trade;</a> to view this file.</small>');
define('OSF_DOC_DOWNLOAD', 'Download Datasheet: ');


require_once('faq_paginator.lang.php');
?>