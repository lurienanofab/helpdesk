<?php
/* *************************************************************************
  Id: FaqUpload.php

  Makes file uploads easier to manage.
  Originally based on a user comment in the php5 manual.

  **********************************************************************

  Updated: 2009-11-05 Tim Gall
  Fixed truncated filename issue

  Updated: 2009-11-06 Tim Gall
  Added new params for reposting form data

  Updated: 2009-11-09 Tim Gall
  Improved functionality for reposting form data.

  **********************************************************************
  USE:
  Set your desired parameters when constructing a new instance.

  Use drawForm() to render the uplad form in html.

  Use processFiles() to process the uploaded files. The params in
  processFiles() enable/disable various elements that may be usefull
  to the person doing the upload including:
    1) a html formatted URL tag to the upload to copy and paste into html content.
    2) a html formatted image tag the user can copy and paste into html content.
    3) A thumbnail image of the upload (for images).
    4) The absolute file path.

  The $file_names[] array will contain the upload names
  after they have been processed.
  *********************************************************************


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FileUpload {
  var $num_of_uploads;
  var $file_types_array;
  var $max_file_size;
  var $upload_fs_dir;
  var $upload_ws_dir;
  var $label;
  var $form_id;
  var $file_names = array();
  var $permissions = 0644;// must be an octal integer, must start with a zero.

  var $trimNameLength = 45;//added & value increased from 15. 2009-11-05 Tim Gall

  var $showUploadBtn = true;
  var $hiddenFields = 0;//name="hidden1", name="hidden2" etc. for each. Used for reposting parent form data
  var $formParams = '';//goes in the form if set
  var $textStar = '<b style="color:red">*</b>';

  /**
   * FaqUpload::__construct()
   *
   * @param mixed $form_id
   * @param integer $num_of_uploads
   * @param mixed $file_types_array
   * @param integer $max_file_size
   * @param string $upload_fs_dir
   * @param string $upload_ws_dir
   * @param string $label
   * @return void
   */
  function __construct($form_id, $num_of_uploads = 1, $file_types_array = array("pdf", "jpg", "png", "gif", "jpeg", "bmp"), $max_file_size = 1048576, $upload_fs_dir = "", $upload_ws_dir = "", $label = "Upload files:") {

    $this->num_of_uploads = $num_of_uploads;
    $this->file_types_array = $file_types_array;
    $this->max_file_size = $max_file_size;
    $this->upload_fs_dir = $upload_fs_dir;
    $this->upload_ws_dir = $upload_ws_dir;
    $this->label = $label;
    $this->form_id = $form_id;

    if (!is_numeric($this->max_file_size)) {
      $this->max_file_size = 1048576;
    }
  }

  /**
   * FaqUpload::drawForm()
   *
   * @return a html upload form as text
   */
  function drawForm() {
    $form = '<form action="' . DIR_FS_WEB_ROOT . basename(OSF_PHP_SELF) . '" method="post" enctype="multipart/form-data" name="' . $this->form_id . '" id="' . $this->form_id . '" '.$this->formParams.'>' . $this->label . '<br /><input type="hidden" name="' . $this->form_id . '" id="' . $this->form_id . '" value="TRUE"><input type="hidden" name="MAX_FILE_SIZE" value="' . $this->max_file_size . '">';

    for ($x = 0; $x < $this->num_of_uploads; $x++) {
      $form .= '<input type="file" name="file[]" />' . $this->textStar . '<br />';
    }

    if($this->showUploadBtn) $form .= '<input type="submit" value="'.OSF_UPLOAD.'" /><br />';

    if($this->hiddenFields > 0){
      for($i = 0; $i < $this->hiddenFields; $i++) $form .= '<input type="hidden" value="" id="hidden'.($i+1). '" name="hidden'.($i+1). '" />';
    }

    $form .= $this->textStar . OSF_VALID_TYPES;
    for ($x = 0; $x < count($this->file_types_array); $x++) {
      if ($x < count($this->file_types_array) - 1) {
        $form .= $this->file_types_array[$x] . ", ";
      } else {
        $form .= $this->file_types_array[$x] . ".";
      }
    }
    $form .= '</form>';
    echo ($form);
  }


  /**
   * FaqUpload::processFiles()
   *
   * @param bool $show_url_code
   * @param bool $show_img_code
   * @param bool $show_thumb_img
   * @param bool $show_fs_path
   * @return void
   */
  function processFiles($show_url_code = true, $show_img_code = false, $show_thumb_img = false, $show_fs_path = false) {
    global $messageHandler;

    if (isset($_POST[$this->form_id]) && isset($_FILES["file"]["error"])) {

      foreach ($_FILES["file"]["error"] as $key => $value) {
        if ($_FILES["file"]["name"][$key] != "") {
          if ($value == UPLOAD_ERR_OK) {
            $origfilename = $_FILES["file"]["name"][$key];
            $filename = explode(".", $_FILES["file"]["name"][$key]);

            $filenameext = $filename[count($filename) - 1];

            //print ('<pre>' . $filenameext . '</pre>');

            unset($filename[count($filename) - 1]);
            $filename = implode(".", $filename);
            $filename = substr($filename, 0, $this->trimNameLength) . "." . $filenameext;

            // moved to after name truncation occurs. 2009-11-05 Tim Gall
            $this->file_names[] = $filename;


            $file_ext_allow = false;
            for ($x = 0; $x < count($this->file_types_array); $x++) {
              if ($filenameext == $this->file_types_array[$x]) {
                $file_ext_allow = true;
                break;
              }
            }
            if ($file_ext_allow) {

              if ($_FILES["file"]["size"][$key] < $this->max_file_size) {
                /* 2010-12-20 Tim Gall
                 *
                 * Added filename translation from utf-8 to ascii for move_uploaded_file()
                 * Im still not sure why this fixes the issue since its not applied to the db name
                 * and the actual saved file still gets saved with a utf-8 name.
                 * However the result seems to stop utf-8 chars in the filenames from getting corrupted.
                 *
                 * Its possible its a bug in php or something related to
                 * the interaction between php and the operating system with file manipulations.
                 */
                if (move_uploaded_file($_FILES["file"]["tmp_name"][$key], $this->upload_fs_dir . FaqFuncs::utf8_to_ascii($filename))) {

                  chmod($this->upload_fs_dir . $filename, $this->permissions);

                  $exampleText = '';
                  $textAreaRows = 0;

                  if ($show_url_code) {
                    $exampleText .= 'URL: <a href="' . $this->upload_ws_dir . $filename . '">' . $filename . '</a>' . "\n";
                    ++$textAreaRows;
                  }
                  if ($show_img_code) {
                    $width_str = '';
                    $height_str = '';
                    if (is_file($this->upload_fs_dir . $filename)) {
                      list($width, $height) = getimagesize($this->upload_fs_dir . $filename);
                      $width_str = ' width="' . $width . '"';
                      $height_str = ' height="' . $height . '"';
                    }
                    $exampleText .= 'IMAGE: <img alt="" src="' . $this->upload_ws_dir . $filename . '"' . $width_str . $height_str . ' />' . "\n";
                    ++$textAreaRows;
                  }
                  if ($show_fs_path) {
                    $exampleText .= "FILE SYSTEM PATH: \n" . $this->upload_fs_dir . $filename;
                    ++$textAreaRows;
                    ++$textAreaRows;
                  }


                  echo ('File: <a href="' . $this->upload_ws_dir . $filename . '" target="_blank">' . $filename . '</a><br />');

                  if ($show_thumb_img)
                    echo '<img alt="' . $filename . '" src="' . $this->upload_ws_dir . $filename . '" width="150" />';
                  if ($textAreaRows > 0) {
                    $formId = $this->form_id . time();
                    echo '<textarea name="' . $formId . '" id="' . $formId . '" wrap="soft" style="font-size:10px" cols="110" rows="' . $textAreaRows . '">' . $exampleText . '</textarea><br /><br />';
                  }

                } else {
                  echo ($origfilename . " was not successfully uploaded<br />");
                }
              } else {
                echo ($origfilename . " was too big, not uploaded<br />");
              }
            } else {
              echo ($origfilename . " had an invalid file extension, not uploaded<br />");
            }
          } else {
            echo ($origfilename . " was not successfully uploaded<br />");
          }
        }
      }
    }
  }
}
?>