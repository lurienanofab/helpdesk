<?php
/* *************************************************************************
  Id: FaqExternal.php

  A collection of functions to
  handle the faq_external functionality.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqExternal{
  public static function list_ext_faqcats($sql_resultset){
    while($ext_cats=db_fetch_array($sql_resultset)){
      $faq_cat = FaqExternal::trim_link_text($ext_cats['category']);

      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ, 'cid=' . $ext_cats['id'], 'SSL', $ext_cats['category']) . '">'.$faq_cat.'</a> (' . $ext_cats['fcount'] . ')<br />';
    }
  }

  public static function list_ext_faqs($sql_resultset){
    while($ext_faqs=db_fetch_array($sql_resultset)){
      $faq_question = FaqExternal::trim_link_text($ext_faqs['question']);

      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ, 'cid=' . $ext_faqs['cid'] . '&answer=' . $ext_faqs['id'], 'SSL', $ext_faqs['question']) . '#f'.$ext_faqs['id'] . '">'.$faq_question.'</a>' . '<br />';
    }
  }

  /**
   * Prep link text for displaying in confined boxes.
   * The length cutoff value is set via admin-settings.
   *
   * @param string $link_text
   * @return string clean $link_text
   */
  private static function trim_link_text($link_text){
    $ret_val = strip_tags($link_text);
    if(strlen($ret_val) > OSFDB_MAX_TXT_LENGTH) $ret_val = substr($ret_val, 0, OSFDB_MAX_TXT_LENGTH-3) . '...';

    return $ret_val;
  }
}
?>