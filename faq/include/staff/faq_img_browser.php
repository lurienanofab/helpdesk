<?php
/* *************************************************************************
 Id: faq_img_browser.php

 Displays images that have previously been uploaded by admins.


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

?>
<style>
<!--
.browse_img{display:block;}
.browse_img img{display:inline; float:left; max-width:100px; max-height:100px;}
.browse_img input[type="submit"]{margin:0; border:2px solid transparent; background:url("<?php echo DIR_WS_IMG; ?>/icons/browse_images.png") no-repeat; width:76px; height:44px;}
.browse_img input[type="submit"]:hover{border:2px solid #999999; background-color:#C1FFA4}
-->
</style>
<div class="browse_img">
<?php
if(isset($_POST['list_images']) && $_POST['list_images']=='true'){
  $images_directory = '../faq/images/';
  $images = array();
  $images_dir = dir($images_directory);

  while (false !== ($image_file = $images_dir->read())) {
    if ( (substr($image_file, 0, 1)!='.') && (substr($image_file, 0, 1)!='_') && (substr($image_file, -4)!='.txt') && (substr($image_file, -4)!='.php') && !is_dir($images_directory . $image_file) ){
      if(is_file($images_directory . $image_file))
        //it seems the php file manipulation functions can mess with utf-8 names
        //so we try to take preventative measures.
        $images[] = FaqFuncs::utf8_to_ascii($image_file, true);
    }
  }
  $images_dir->close();

  foreach($images as $img){
    echo '<img src="' . DIR_WS_IMAGES . $img . '" alt="'.$img.'" title="'.$img.'" />';
  }

}else{
  echo '<form action="./faq_assist.php?img_browse=true" method="post">';
  echo '<input type="hidden" name="list_images" id="list_images" value="true" />';
  echo '<input type="submit" name="submit" id="submit" value="" title="' . OSF_IMAGE_BROWSE . '" />';
  echo '</form>';
}
?>
</div>