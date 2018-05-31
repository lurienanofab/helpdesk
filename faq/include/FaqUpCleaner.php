<?php
/* *************************************************************************
  Id: FaqUpCleaner.php

  Find FAQ images and documents that are stored locally but not used in any Faqs.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

/**
 * FaqUpCleaner
 * Faq'n faqs :-)
 *
 * Find Faq images and documents that are stored locally but not used in any Faqs.
 *
 * @package Faqs
 * @author Tim Gall
 * @copyright 2009-2011
 * @version 2.0
 * @access public
 */
class FaqUpCleaner {

  function __construct(){

  }

  /**
   * find all valid image references in FAQ 'answer' text
   *
   * @return Array of filenames
   */
  function findValidImages() {
    $validImages = array();
    $splitChunk;
    $validImg;

    $images_query = db_query("SELECT answer FROM ".TABLE_FAQS." WHERE answer LIKE '%src=\"%\"%';");
    while ($images_res = db_fetch_array($images_query)) {
      preg_match_all('/src="(.*)"/iUms', $images_res['answer'], $matches);

      foreach ($matches[1] as $img) {
        $validImg = substr($img, strrpos($img, '/') + 1);
        // omit any garbage and make sure its a valid filename
        $validImg = urldecode($validImg);
        if (!in_array($validImg, $validImages)) {
          $validImages[] = $validImg . (is_file(DIR_FS_IMAGES . FaqFuncs::utf8_to_ascii($validImg)) ? '' : ' (<span class="osf_red">'.OSF_REMOTE_OR_MISSING.'</span>)');
        }
      }
    }
    sort($validImages, SORT_STRING);
    reset($validImages);

    return $validImages;
  }

  /**
   * find all unused images in FAQ uploads directory
   *
   * @return Array of filenames
   */
  function findUnusedImages($validImages = array()) {
    $removeImages = array();
    $dir = dir(DIR_FS_IMAGES);
    while ($single_file = $dir->read()) {
      $single_file_utf8 = FaqFuncs::utf8_to_ascii($single_file, true);
      if ((substr($single_file, 0, 1)!='.') && (substr($single_file, 0, 1)!='_') && (substr($single_file, -4) != '.txt') && ($single_file != 'index.php') && !in_array($single_file, $validImages)){
        if(is_file(DIR_FS_IMAGES . $single_file_utf8)){
          if($single_file_utf8===$single_file)
            $removeImages[] = $single_file_utf8;
          else
            $removeImages[] = $single_file . ' (<span class="osf_red">'.OSF_DAMAGED_NAME.'</span>)';
        }else{
          //this else block could probably be removed.
          if($single_file_utf8==$single_file) $removeImages[] = $single_file_utf8;
        }
      }
    }
    $dir->close();
    reset($removeImages);

    return $removeImages;
  }




  function findValidPdfs() {
    $validPdfs = array();
    $splitChunk;
    $validP;

    $pdf_query = db_query("SELECT pdfupload FROM ".TABLE_FAQS.";");
    while ($pdf_res = db_fetch_array($pdf_query)) {
		$validP = $pdf_res['pdfupload'];
	    // omit any garbage and make sure its a valid filename
	    if (is_file(DIR_FS_DOC . $validP) && !in_array($validP, $validPdfs)) {
	      $validPdfs[] = $validP;
	    }
    }
    sort($validPdfs, SORT_STRING);
    reset($validPdfs);

    return $validPdfs;
  }


  function findUnusedPdfs($validPdfs = array()) {
    $removePdfs = array();
    $dir = dir(DIR_FS_DOC);
    while ($single_file = $dir->read()) {
      if ((substr($single_file, 0, 1)!='.') && (substr($single_file, 0, 1)!='_') && (substr($single_file, -4) != '.txt') && ($single_file != 'index.php') && !in_array($single_file, $validPdfs)) {

        if(FaqFuncs::utf8_to_ascii($single_file, true) != $single_file) $single_file .= ' (<span class="osf_red">'.OSF_DAMAGED_NAME.'</span>)';
        $removePdfs[] = $single_file;
      }
    }
    $dir->close();
    reset($removePdfs);

    return $removePdfs;
  }
}
?>