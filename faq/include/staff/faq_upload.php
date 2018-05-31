<?php
/* *************************************************************************
 Id: upload-file.php

 AJAX backend for image uploads


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

$fname = basename($_FILES['img']['name']);
// relative to this file
$filename = realpath('../faq/images') . '/' . $fname;

// validate file extension serverside
$extensions = array('jpg','png','jpeg','gif');
$img_extension = substr(strtolower($fname), strrpos($fname, '.')+1);
$ext_found = false;
foreach($extensions as $ext){
  if($img_extension==$ext){
    $ext_found = true;
    break;
  }
}

//server errors will be displayed in the js error results
if (($_FILES['img']['error'] == UPLOAD_ERR_OK) && $ext_found && move_uploaded_file($_FILES['img']['tmp_name'], FaqFuncs::utf8_to_ascii($filename))) {
	echo "success";
} else {
	if(!$ext_found){
	  echo "error_extension";
	}else{
    echo "error";
	}
}
?>