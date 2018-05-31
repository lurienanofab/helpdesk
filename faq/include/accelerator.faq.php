<?php
/* *************************************************************************
  Id: accelerator.faq.php

  Accelerator (for php).
  Broooom broooom. Make pages display faster.

  Compress server output if browser supports it.
  Add ETag's and caching headers to php pages.


  Tim Gall
  Copyright (c) 2009 Oz-DevWorX.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */


ob_start("osfAccelerator");


/**
 * Callback function for ob_start()
 * Provides compression (when suitable) and performance oriented
 * cache control.
 *
 * @param string $buffer The servers output buffer
 * @return string - the contents from the servers output buffer.
 */
function osfAccelerator($buffer) {
  $power = 'osFaq';//header tracking
  $buffer = trim($buffer);

  $original_length = strlen($buffer);
  $compress_me = FALSE;

  if(extension_loaded('zlib') && (!ini_get('zlib.output_compression') || strtolower(ini_get('zlib.output_compression'))=='off') && ini_get('output_handler') != 'ob_gzhandler' && $original_length >= 4096){
    if(find_match(getenv("HTTP_ACCEPT_ENCODING"), 'gzip') || find_match(getenv("HTTP_ACCEPT_ENCODING"), 'x-gzip')){
      $buffer = gzencode($buffer, 3, FORCE_GZIP);
      $compress_me = 'gzip';
    } elseif(find_match(getenv("HTTP_ACCEPT_ENCODING"), 'deflate')){
      $buffer = gzcompress($buffer, 3);
      $compress_me = 'deflate';
    }

    if($compress_me!=FALSE){
      header('Vary: Accept-Encoding', TRUE);
      header('Content-Encoding: ' . $compress_me, TRUE);
      header('Content-Length: ' . strlen($buffer), TRUE); // keep-alive trigger
    }
  }

  $etag = '"' . md5($buffer) . '"';
  header('ETag: ' . $etag); //eTag should be in all responses including 304

  // Allow recipient machines to temporarily cache pages.
  // Fresh content will be loaded if the ETag changes/expires or is not returned by the browser
  header('Cache-Control: no-transform, must-revalidate, post-check=0, pre-check=0');

  if (find_match(getenv('HTTP_IF_NONE_MATCH'), $etag)) {
    header(getenv('SERVER_PROTOCOL') . ' 304 Not Modified');
    $buffer = null;
  } else {
    header('Pragma: '); //clears the deprecated pragma header.
    header('X-Powered-By: ' . $power);
    //header('X-Powered-By: ' . $power . '/zlib:' . $compress_me . '/original:' . $original_length . '/compressed:' . strlen($buffer));
  }

  //see: http://php.net/manual/en/function.ob-start.php
  chdir(dirname($_SERVER['SCRIPT_FILENAME']));

  return $buffer;
}

/**
 * Find a value in a delimited string
 *
 * @param string $search - The value to search
 * @param string $match - The value to look for
 * @param string $dlmr - The $search string delimiter seperating entries
 * @return boolean - true if a match is found.
 */
function find_match($search, $match, $dlmr = ',') {
  $tok_set = explode($dlmr, $search);
  foreach ($tok_set as $tok)
    if (trim($tok) == $match) return true;
  return false;
}
?>