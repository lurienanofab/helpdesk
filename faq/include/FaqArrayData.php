<?php
/* *************************************************************************
  Id: FaqArrayData.php

  Simple array container. Primarily for normalising various array inputs
  so programatic use becomes standard regardless of the original array souce.
  This class can handle single and 2 dimensional arrays as input.
  Output structure will be the same as was input.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqArrayData {

  /**
   * If your only adding a single array, use this construct param.
   *
   * @param array $objArray
   * @return void
   */
  function __construct($objArray=array()) {
    $this->set_array($objArray);
  }

  function __destruct() {
    unset($this);
  }

  /**
   * Add a single dimension array to this Set. This can be used to add a single key=>value at a time.
   * Duplicates will overwrite any existing values.
   * Existing values will be over written if any keys in $objArray already exist in this Set.
   * @param $objArray - in the form of array($key=>$value, etc.)
   */
  function set_array($objArray) {
    foreach ($objArray as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * If you want to add multiple arrays with the same keys add arrays using this method.
   * Its still OK to pass in an array to the constructor, however it is optional.
   *
   * $this will now contain a 2 dimensional array with the
   * keys occupying the first dimension and values in the second dimension.
   *
   * @param array $objArray
   * @return void
   */
  function add_array($objArray) {
    foreach ($objArray as $key => $value) {
      if(!is_array($this->$key)){
        if(isset($this->$key)){
          $singleVal = $this->$key;
          $this->$key = array($singleVal);
        }else{
          $this->$key = array();
        }
      }
      array_push($this->$key, $value);
    }
  }

  /**
   * If you call this method after adding values with add_array() you will get a 2 dimensional array of $key=>$vals[].
   * If the data was only added via the constructor or set_array(), you will get an iterable list of key=>val.
   * foreach works fine for reading this methods output in both cases.
   *
   * @return array - All variables contained in this class.
   */
  function get_array(){
    return $this;
  }
}
?>