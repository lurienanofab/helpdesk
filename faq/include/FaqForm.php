<?php
/* *************************************************************************
 Id: FaqForm.php

 Convenience class for generating form compnents.


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

class FaqForm {

  public static $tfRequired;

  /**
   * FaqForm::__construct()
   *
   * @return
   */
  function __construct() {
    $tfRequired = ' <b style="color:red;">*</b>';
  }

  /**
   * Outputs a form button
   *
   * @param mixed $image - ignored in this release.
   * @param string $title - Visible button text
   * @param string $parameters - [optional] additional html params
   * @return 1 form submit button
   */
  function submit_image($image, $title = '', $parameters = '') {
//    if(OSFDB_FANCY_BUTTONS=='true'){
//      $image_submit = '<input type="image" src="' . FaqFuncs::output_string(DIR_WS_BUTTONS . $image) . '" border="0" alt="' . FaqFuncs::output_string($title) . '"';
//    }else{
      $image_submit = '<input type="submit" class="osf_button" value="' . FaqFuncs::output_string($title) . '"';
//    }

    if (FaqFuncs::not_null($title)) $image_submit .= ' title=" ' . FaqFuncs::output_string($title) . ' "';
    if (FaqFuncs::not_null($parameters)) $image_submit .= ' ' . $parameters;
    $image_submit .= '>';
    return $image_submit;
  }

  /**
   * Output a button suitable for wrapping in a html link.
   *
   * @param mixed $image - ignored in this release.
   * @param string $title - Visible button text
   * @param string $params - [optional] additional html params
   * @return 1 button themed div for wrapping in a html link
   */
  function button_image($image, $title = '', $params = '') {
//    $button = '';
//    if(OSFDB_FANCY_BUTTONS=='true'){
//      $button = FaqFuncs::format_image(DIR_WS_BUTTONS . $image, $title, '', '', $params);
//    }else{
      $button = '<div class="osf_button"';
      if (FaqFuncs::not_null($title)) $button .= ' title=" ' . FaqFuncs::output_string($title) . ' "';
      if (FaqFuncs::not_null($parameters)) $button .= ' ' . $parameters;
      $button .= '>'. FaqFuncs::output_string($title) . '</div>';
//    }
    return $button;
  }

  /**
   * Output a form opening tag
   *
   * @param mixed $name
   * @param mixed $action
   * @param string $parameters
   * @param string $method
   * @param string $params
   * @param string $link_type
   * @return
   */
  function form_open($name, $action, $parameters = '', $method = 'post', $params = '', $link_type = 'NONSSL') {
    $form = '<form name="' . FaqFuncs::output_string($name) . '" id="' . FaqFuncs::output_string($name) . '" action="';

    // added ssl failsafe to form urls
    if (getenv('HTTPS') == 'on') {
      $link_type = 'SSL';
    }

    $form .= FaqFuncs::format_url($action, $parameters, $link_type);
    $form .= '" method="' . FaqFuncs::output_string($method) . '"';
    if (FaqFuncs::not_null($params)) {
      $form .= ' ' . $params;
    }
    $form .= '>';
    return $form;
  }

  /**
   * Output a form input field
   *
   * @param mixed $name
   * @param string $value
   * @param string $parameters
   * @param bool $required
   * @param string $type
   * @param bool $reinsert_value
   * @return
   */
  function input_field($name, $value = '', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $field = '';

    $field .= '<input type="' . FaqFuncs::output_string($type) . '" name="' . FaqFuncs::output_string($name) . '" id="' . FaqFuncs::output_string($name) . '"';
    if (isset($_GET[$name]) && ($reinsert_value == true) && is_string($_GET[$name])) {
      $field .= ' value="' . FaqFuncs::output_string(stripslashes($_GET[$name])) . '"';
    } else
    if (isset($_POST[$name]) && ($reinsert_value == true) && is_string($_POST[$name])) {
      $field .= ' value="' . FaqFuncs::output_string(stripslashes($_POST[$name])) . '"';
    } else
    if (FaqFuncs::not_null($value)) {
      $field .= ' value="' . FaqFuncs::output_string($value) . '"';
    }
    if (FaqFuncs::not_null($parameters))
    $field .= ' ' . $parameters;
    $field .= '>';

    if ($required == true)
    $field .= FaqForm::$tfRequired;

    return $field;
  }

  /**
   * Output a selection field - alias function for checkbox_field() and radio_field()
   *
   * @param mixed $name
   * @param mixed $type
   * @param string $value
   * @param bool $checked
   * @param string $compare
   * @param string $parameter
   * @return
   */
  function selection_field($name, $type, $value = '', $checked = false, $compare = '', $parameter = '') {
    $selection = '<input type="' . $type . '" name="' . $name . '" id="' . $name . '"';
    if ($value != '') {
      $selection .= ' value="' . $value . '"';
    }
    if (($checked == true) || ($_GET[$name] == 'on') || ($value && ($_GET[$name] == $value)) || ($value && ($value == $compare))) {
      $selection .= ' checked="checked"';
    } else
    if (($checked == true) || ($_POST[$name] == 'on') || ($value && ($_POST[$name] == $value)) || ($value && ($value == $compare))) {
      $selection .= ' checked="checked"';
    }
    if ($parameter != '') {
      $selection .= ' ' . $parameter;
    }
    $selection .= '>';
    return $selection;
  }

  /**
   * Output a form checkbox field
   *
   * @param mixed $name
   * @param string $value
   * @param bool $checked
   * @param string $compare
   * @param string $parameter
   * @return
   */
  function checkbox_field($name, $value = '', $checked = false, $compare = '', $parameter = '') {
    return $this->selection_field($name, 'checkbox', $value, $checked, $compare, $parameter);
  }

  /**
   * Output a form radio field
   *
   * @param mixed $name
   * @param string $value
   * @param bool $checked
   * @param string $compare
   * @param string $parameter
   * @return
   */
  function radio_field($name, $value = '', $checked = false, $compare = '', $parameter = '') {
    return $this->selection_field($name, 'radio', $value, $checked, $compare, $parameter);
  }

  /**
   * Output a form textarea field
   *
   * @param mixed $name
   * @param mixed $wrap
   * @param mixed $width
   * @param mixed $height
   * @param string $text
   * @param string $parameters
   * @param bool $reinsert_value
   * @return
   */
  function textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . FaqFuncs::output_string($name) . '" id="' . FaqFuncs::output_string($name) . '" wrap="' . FaqFuncs::output_string($wrap) . '" cols="' . FaqFuncs::output_string($width) . '" rows="' . FaqFuncs::output_string($height) . '"';
    if (FaqFuncs::not_null($parameters))
    $field .= ' ' . $parameters;
    $field .= '>';
    if ((isset($_GET[$name])) && ($reinsert_value == true)) {
      $field .= stripslashes($_GET[$name]);
    } else
    if ((isset($_POST[$name])) && ($reinsert_value == true)) {
      $field .= stripslashes($_POST[$name]);
    }
    $field .= $text;
    $field .= '</textarea>';

    return $field;
  }

  /**
   * Output a form hidden field
   *
   * @param mixed $name
   * @param string $value
   * @param string $parameters
   * @return
   */
  function hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . FaqFuncs::output_string($name) . '"';
    if (FaqFuncs::not_null($value)) {
      $field .= ' value="' . FaqFuncs::output_string($value) . '"';
    } else
    if (isset($_GET[$name]) && is_string($_GET[$name])) {
      $field .= ' value="' . FaqFuncs::output_string(stripslashes($_GET[$name])) . '"';
    } else
    if (isset($_POST[$name]) && is_string($_POST[$name])) {
      $field .= ' value="' . FaqFuncs::output_string(stripslashes($_POST[$name])) . '"';
    }
    if (FaqFuncs::not_null($parameters))
    $field .= ' ' . $parameters;
    $field .= '>';
    return $field;
  }

  /**
   * Output a form pull down menu
   *
   * @param mixed $name
   * @param mixed $values
   * @param string $default
   * @param string $parameters
   * @param bool $required
   * @return
   */
  function pulldown_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . FaqFuncs::output_string($name) . '" id="' . FaqFuncs::output_string($name) . '"';
    if (FaqFuncs::not_null($parameters)) {
      $field .= ' ' . $parameters;
    }
    $field .= '>';
    if (empty($default) && isset($_GET[$name])){
      $default = stripslashes($_GET[$name]);
    }elseif (empty($default) && isset($_POST[$name])) {
      $default = stripslashes($_POST[$name]);
    }

    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $field .= '<option value="' . FaqFuncs::output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }
      $field .= '>' . FaqFuncs::output_string($values[$i]['text']) . '</option>';
    }
    $field .= '</select>';
    if ($required == true){
      $field .= FaqForm::$tfRequired;
    }
    return $field;
  }

}
?>