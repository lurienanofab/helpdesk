<?php
/* *************************************************************************
  Id: faq_admin_ui.lang.php

  Language file for staff/faq_admin_ui.inc.php


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

define('OSF_MAIN_TITLE', 'Frequently Asked Questions');

// TOOLTIPS/BUTTON TEXT
define('OSF_TIP_FOLDER', 'Category Folder');
define('OSF_TIP_PREVIEW', 'Preview');
define('OSF_TIP_COPY', 'Copy');
define('OSF_TIP_COPY_TO', 'Copy');
define('OSF_TIP_DELETE', 'Delete');
define('OSF_TIP_MOVE', 'Move');
define('OSF_TIP_NEW_CAT', 'New Category');
define('OSF_TIP_NEW_FAQ', 'New Faq');
define('OSF_TIP_STATUS_GREEN', 'Active');
define('OSF_TIP_STATUS_GREEN_LIGHT', 'Set Active');
define('OSF_TIP_STATUS_RED', 'Inactive');
define('OSF_TIP_STATUS_RED_LIGHT', 'Set Inactive');

define('OSF_EMPTY_CATEGORY', 'Empty Category');

define('OSF_ERROR_CANNOT_LINK_TO_SAME_CATEGORY', 'Error: Can not link faqs in the same category.');
define('OSF_ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT', 'Error: Category cannot be moved into child category.');


define('OSF_HEAD_AUTHOR_DETAIL', 'Authors Details');
define('OSF_HEAD_FAQ_DETAIL', 'FAQ Details');
define('OSF_HEAD_NO_SELECTION', 'NOTHING SELECTED');
define('OSF_HEAD_TITLE_GOTO', 'Go To:');
define('OSF_HEAD_TITLE_SEARCH', 'Search:');
define('OSF_HEAD_ACTION', 'Action');
define('OSF_HEAD_CATS_FAQS', 'Frequently Asked Questions');
define('OSF_HEAD_FEATURED', 'Feature');
define('OSF_HEAD_INFO_EDIT_CATEGORY', 'Edit Category in [ %s ]');
define('OSF_HEAD_INFO_MOVE_CATEGORY', 'Move Category');
define('OSF_HEAD_INFO_MOVE_FAQ', 'Move FAQ');
define('OSF_HEAD_INFO_NEW_CATEGORY', 'New Category in [ %s ]');
define('OSF_HEAD_STATUS', 'Status');
define('OSF_HEAD_INFO_COPY_TO', 'Copy To');
define('OSF_HEAD_INFO_DELETE_CATEGORY', 'Delete Category');
define('OSF_HEAD_INFO_DELETE_FAQ', 'Delete FAQ');
define('OSF_HEAD_VIEWS', 'Views');
define('OSF_HEAD_SORT_ASC', 'Sort ascending');
define('OSF_HEAD_SORT_DESC', 'Sort decending');

define('OSF_ANY_STATUS', 'Any Status');
define('OSF_CAT_NAME', 'Category Name:');
define('OSF_CATEGORIES', 'FAQ Categories:');
define('OSF_CLEAR_SEARCH', 'Clear Search');
define('OSF_COPY_AS_DUPLICATE', 'Duplicate faq');
define('OSF_COPY_AS_LINK', 'Link faq');
define('OSF_DATE_ADDED', 'Date Added: ');

define('OSF_DELETE_CAT_INTRO', 'Are you sure you want to delete this category?');
define('OSF_DELETE_FAQ_INTRO', 'Are you sure you want to permanently delete this FAQ?');
define('OSF_DELETE_WARNING_CHILDS', '<b>WARNING:</b> There are %s (child-)categories still linked to this category!');
define('OSF_DELETE_WARNING_FAQS', '<b>WARNING:</b> There are %s faqs still linked to this category!');
define('OSF_DISABLED', 'Disabled');
define('OSF_DOCUMENT_UPLOAD', 'Document files will be displayed at the bottom of the FAQ answer content.');
define('OSF_EDIT_FAQ', 'Edit FAQ in [ %s ]');
define('OSF_ENABLED', 'Enabled');
define('OSF_FAQ_ANSWER', 'Answer:');
define('OSF_FAQ_AUTHOR', 'Authors Name:');
define('OSF_FAQ_AVAILABLE', 'Active');
define('OSF_DATE_ADDED', 'Date Added: ');
define('OSF_FAQ_EMAIL', 'Authors Email:');
define('OSF_NOT_AVAILABLE', 'Disabled');
define('OSF_DOCUMENT', 'Document: ');
define('OSF_FAQ_PHONE', 'Authors Phone:');
define('OSF_QUESTION', 'Question:');
define('OSF_FAQS', 'FAQs:');
define('OSF_STATUS', 'Status:');
define('OSF_FREE_BROWSE_MODE', 'New categories and FAQs<br />can\'t be created when<br />in SHOW-ALL mode.');
define('OSF_HOW_TO_COPY', 'Copy Method:');
define('OSF_INFO_COPY_TO_INTRO', 'Please choose a new category you wish to copy this faq to');
define('OSF_INFO_CURRENT_CATEGORIES', 'Current Categories:');

define('OSF_TEXT_IMAGES', 'Upload Images');
define('OSF_IMAGE_UPLOADS', 'Convenience file uploader for inline images.<br /><ol>  <li>Upload image/s using the <b>Upload Images</b> button.</li>  <li>Drag the thumbnail (right) into your document (above).</li>  <li>To edit the image attributes, double click the image in your document (above).</li>  <li>To view images youve already uploaded, click the browse-image-icon.</li> </ol>');
define('OSF_UPLOAD', 'Upload');
define('OSF_VALID_TYPES', 'Valid file type(s): ');
define('OSF_TYPES_ALLOWED', 'Only JPG, PNG or GIF files are allowed');
define('OSF_UPLOADING', 'Uploading...');
define('OSF_FAILED', 'Upload Failed');
define('OSF_IMAGE_BROWSE', 'Browse images');

define('OSF_LAST_MODIFIED', 'Last Modified: ');
define('OSF_TEXT_MOVE', 'Move <b>%s</b> to:');
define('OSF_INTRO_MOVE_CATEGORIES', 'Please select which category you wish <b>%s</b> to reside in');
define('OSF_MOVE_FAQS_INTRO', 'Please select which category you wish <b>%s</b> to reside in');
define('OSF_HEAD_NEW_FAQ', 'New FAQ for [ %s ]');
define('OSF_NO_CHILDS', 'Please insert a new category or faq in this level.');
define('OSF_TEXT_PRIVATE', ' <i>(private)</i>');
define('OSF_TEXT_PUBLIC', ' <i>(public)</i>');
define('OSF_REMOVE_DOC', ' check here to remove the document');
define('OSF_SELECT_A_ROW', 'Please select a row');
define('OSF_SHOW_ALL', 'Show all:');
define('OSF_SUBCATEGORIES', 'FAQ Subcategories:');
define('OSF_INFO_TOP', 'FAQs can be added to any subcategory of <b>' . OSF_TEXT_TOP . '</b>.');
define('OSF_INFO_SEARCH', 'New FAQs can\'t be added to search results. Clear your search and navigate to a category to add new categories or FAQs.');
define('OSF_DOC_FOR_REMOVAL', 'marked for removal');
define('OSF_DOC_FOR_UPLOAD', 'ready for uploading');
define('OSF_BTN_YES', 'yes');
define('OSF_BTN_NO', 'no');

define('OSF_WARN_QUESTION_EMPTY', 'The QUESTION field must contain at least 1 character.<br />Press the BACK button to make changes.');
define('OSF_WARN_ANSWER_EMPTY', 'The ANSWER field must contain at least 1 character.<br />Press the BACK button to make changes.');
define('OSF_WARN_CAT_EMPTY', 'Please check the following form fields and try again.\n\n* A Category name is required. Min 2 characters\n');
?>