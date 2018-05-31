<?php
/* *************************************************************************
  Id: FaqCrumb.php

  Simple FaqCrumb-trail wrapper class for building FaqCrumb links.
  NOTE: sorting needs to be done during input.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqCrumb {
  var $trail;

  function __construct() {
    $this->trail = array();
  }

  function add($title, $link = '') {
    $this->trail[] = array('title' => $title, 'link' => $link);
  }

  function get($separator = ' - ') {
    $trail_string = '';
    foreach ($this->trail as $link) {
      if (isset($link['link']) && FaqFuncs::not_null($link['link'])) {
        $trail_string .= '<a href="' . $link['link'] . '">' . $link['title'] . '</a>';
      } else {
        $trail_string .= $link['title'];
      }
      $trail_string .= $separator;
    }
    // remove trailing seperator
    return substr($trail_string, 0, strlen($trail_string) - strlen($separator));
  }
}
?>