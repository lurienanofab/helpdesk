<?php
/* *************************************************************************
  Id: FaqAdmin.php

  A collection of functions to
  handle most of the specialised FAQ admin functionality.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqAdmin {

  /**
   * FaqAdmin::__construct()
   *
   * @return
   */
  function __construct() {
//    if (!defined('OSF_TEXT_TOP'))
//      define('OSF_TEXT_TOP', 'TOP');
  }

  function show_bc_menu(){
    global $FaqCrumb, $current_faq_cat_id, $fcPath;


    $FaqCrumb->add(OSF_TEXT_TOP, FaqFuncs::format_url(FILE_FAQ_ADMIN, '', 'SSL'));

    if(isset($_GET['search']) && FaqFuncs::not_null($_GET['search'])) {
      $search_str = db_input($_GET['search'], false);
      $FaqCrumb->add('Search for: '.$search_str, FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('action', 'page', 'fcPath')), 'SSL'));
    }else{

    	/// collect parent category data
    	if($current_faq_cat_id > 0){
    		$parent_cats_array = FaqFuncs::faq_get_parent_tree($current_faq_cat_id, '', '', '', FILE_FAQ_ADMIN);

    		if(is_array($parent_cats_array)){
    			for ($i=0; $i<sizeof($parent_cats_array); $i++) {
    				$FaqCrumb->add($parent_cats_array[$i]['title'], $parent_cats_array[$i]['link']);
    			}
    		}
    		$FaqCrumb->add(FaqFuncs::get_cat_name($current_faq_cat_id), FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('action', 'page', 'fcPath')) . 'fcPath='.$fcPath, 'SSL'));
    	}
    }

    echo $FaqCrumb->get(' &raquo; ');
  }

  /**
   * FaqAdmin::get_tree()
   *
   * @param string $parent_id
   * @param string $spacing
   * @param string $exclude
   * @param string $faq_tree_array
   * @param bool $include_itself
   * @return
   */
  function get_tree($parent_id = '0', $spacing = '', $exclude = '', $faq_tree_array = '', $include_itself = false) {
    if (!is_array($faq_tree_array))
      $faq_tree_array = array();
    if ((sizeof($faq_tree_array) < 1) && ($exclude != '0'))
      $faq_tree_array[] = array('id' => '0', 'text' => OSF_TEXT_TOP);
    if ($include_itself) {
      $faq_category_query = db_query("select category from ".TABLE_FAQCATS." where id = '" . (int)$parent_id . "'");
      $faq_category = db_fetch_array($faq_category_query);
      $faq_tree_array[] = array('id' => $parent_id, 'text' => $faq_category['category']);
    }

    $faq_category_query = db_query("select id, parent_id, category from ".TABLE_FAQCATS." where parent_id = '" . (int)$parent_id . "' order by category");
    while ($faq_category = db_fetch_array($faq_category_query)) {
      if ($exclude != $faq_category['id'])
        $faq_tree_array[] = array('id' => $faq_category['id'], 'text' => $spacing . $faq_category['category']);
      $faq_tree_array = $this->get_tree($faq_category['id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $faq_tree_array);
    }
    return $faq_tree_array;
  }

  /**
   * FaqAdmin::count_subcats_in_cat()
   *
   * @param mixed $categories_id
   * @return
   */
  function count_subcats_in_cat($categories_id) {
    $categories_count = 0;
    $categories_query = db_query("select id from ".TABLE_FAQCATS." where parent_id = '" . (int)$categories_id . "'");
    while ($categories = db_fetch_array($categories_query)) {
      $categories_count++;
      $categories_count += $this->count_subcats_in_cat($categories['id']);
    }
    return $categories_count;
  }

  /**
   * FaqAdmin::get_output_cat_path()
   *
   * @param mixed $id
   * @param string $from
   * @return
   */
  function get_output_cat_path($id, $from = 'faqcategories') {
    $calculated_category_path_string = '';
    $calculated_category_path = $this->get_cat_path_array($id, $from);
    for ($i = 0, $n = sizeof($calculated_category_path); $i < $n; $i++) {
      for ($j = 0, $k = sizeof($calculated_category_path[$i]); $j < $k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . '<br>';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);
    if (strlen($calculated_category_path_string) < 1)
      $calculated_category_path_string = OSF_TEXT_TOP;
    return $calculated_category_path_string;
  }

  /**
   * FaqAdmin::get_cat_path_array()
   *
   * @param mixed $id
   * @param string $from
   * @param string $categories_array
   * @param integer $index
   * @return
   */
  function get_cat_path_array($id, $from = 'faqcategories', $categories_array = '', $index = 0) {
    if (!is_array($categories_array))
      $categories_array = array();
    if ($from == 'faqs') {
      $categories_query = db_query("select faqcategory_id from ".TABLE_FAQS2FAQCATS." where faq_id = '" . (int)$id . "'");
      while ($categories = db_fetch_array($categories_query)) {
        if ($categories['faqcategory_id'] == '0') {
          $categories_array[$index][] = array('id' => '0', 'text' => OSF_TEXT_TOP);
        } else {
          $category_query = db_query("select id, category, parent_id from ".TABLE_FAQCATS." where id = '" . (int)$categories['faqcategory_id'] . "'");
          $category = db_fetch_array($category_query);
          $categories_array[$index][] = array('id' => $category['id'], 'text' => $category['category']);
          if ((FaqFuncs::not_null($category['parent_id'])) && ($category['parent_id'] != '0'))
            $categories_array = $this->get_cat_path_array($category['parent_id'], 'faqcategories', $categories_array, $index);
          $categories_array[$index] = array_reverse($categories_array[$index]);
        }
        $index++;
      }
    } else
      if ($from == 'faqcategories') {
        $category_query = db_query("select category, parent_id from ".TABLE_FAQCATS." where id = '" . (int)$id . "'");
        $category = db_fetch_array($category_query);
        $categories_array[$index][] = array('id' => $id, 'text' => $category['category']);
        if ((FaqFuncs::not_null($category['parent_id'])) && ($category['parent_id'] != '0'))
          $categories_array = $this->get_cat_path_array($category['parent_id'], 'faqcategories', $categories_array, $index);
      }
    return $categories_array;
  }

  /**
   * FaqAdmin::get_generated_cat_ids()
   *
   * @param mixed $id
   * @param string $from
   * @return
   */
  function get_generated_cat_ids($id, $from = 'faqcategories') {
    $calculated_category_path_string = '';
    $calculated_category_path = $this->get_cat_path_array($id, $from);
    for ($i = 0, $n = sizeof($calculated_category_path); $i < $n; $i++) {
      for ($j = 0, $k = sizeof($calculated_category_path[$i]); $j < $k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -1) . '<br>';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -4);
    if (strlen($calculated_category_path_string) < 1)
      $calculated_category_path_string = OSF_TEXT_TOP;
    return $calculated_category_path_string;
  }

  /**
   * FaqAdmin::get_faq_path()
   *
   * @param string $current_faq_cat_id
   * @return
   */
  function get_faq_path($current_faq_cat_id = '') {
    global $fcPath_array;
    if ($current_faq_cat_id == '') {
      $fcPath_new = implode('_', $fcPath_array);
    } else {
      if (sizeof($fcPath_array) == 0) {
        $fcPath_new = $current_faq_cat_id;
      } else {
        $fcPath_new = '';
        $last_category_query = db_query("select parent_id from ".TABLE_FAQCATS." where id = '" . (int)$fcPath_array[(sizeof($fcPath_array) - 1)] . "'");
        $last_category = db_fetch_array($last_category_query);
        $current_category_query = db_query("select parent_id from ".TABLE_FAQCATS." where id = '" . (int)$current_faq_cat_id . "'");
        $current_category = db_fetch_array($current_category_query);
        if ($last_category['parent_id'] == $current_category['parent_id']) {
          for ($i = 0, $n = sizeof($fcPath_array) - 1; $i < $n; $i++) {
            $fcPath_new .= '_' . $fcPath_array[$i];
          }
        } else {
          for ($i = 0, $n = sizeof($fcPath_array); $i < $n; $i++) {
            $fcPath_new .= '_' . $fcPath_array[$i];
          }
        }
        $fcPath_new .= '_' . $current_faq_cat_id;
        if (substr($fcPath_new, 0, 1) == '_') {
          $fcPath_new = substr($fcPath_new, 1);
        }
      }
    }
    return 'fcPath=' . $fcPath_new;
  }

  /**
   * FaqAdmin::count_faqs_in_cat()
   *
   * @param mixed $categories_id
   * @param bool $include_deactivated
   * @return
   */
  function count_faqs_in_cat($categories_id, $include_deactivated = false) {
    $products_count = 0;
    if ($include_deactivated) {
      $faqs_query = db_query("select count(*) as total from ".TABLE_FAQS2FAQCATS." where faqcategory_id = '" . (int)$categories_id . "'");
    } else {
      $faqs_query = db_query("select count(*) as total from ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f where f.faq_active = '1' and f2f.faqcategory_id = '" . (int)$categories_id . "' and f.id = f2f.faq_id");
    }
    $faqs = db_fetch_array($faqs_query);
    $faqs_count += $faqs['total'];
    $childs_query = db_query("select id from ".TABLE_FAQCATS." where parent_id = '" . (int)$categories_id . "'");
    if (db_num_rows($childs_query)) {
      while ($childs = db_fetch_array($childs_query)) {
        $faqs_count += $this->count_faqs_in_cat($childs['id'], $include_deactivated);
      }
    }
    return $faqs_count;
  }

  /**
   * FaqAdmin::set_status()
   *
   * @param mixed $faq_id
   * @param mixed $status
   * @return
   */
  function set_status($faq_id, $status) {
    if ($status == '1') {
      return db_query("update ".TABLE_FAQS." set faq_active = '1', last_modified = now() where id = '" . (int)$faq_id . "'");
    } elseif ($status == '0') {
      return db_query("update ".TABLE_FAQS." set faq_active = '0', last_modified = now() where id = '" . (int)$faq_id . "'");
    } else {
      return - 1;
    }
  }

  /**
   * Change the favorite flag for a FAQ.
   * The last_modified field is not updated because it affects the
   * newest FAQ display in the external FAQ box.
   *
   * @param mixed $faq_id
   * @param mixed $status
   * @return
   */
  function set_favorite($faq_id, $status) {
    if ($status == '1') {
      return db_query("update ".TABLE_FAQS." set show_on_nonfaq = '1' where id = '" . (int)$faq_id . "'");
    } elseif ($status == '0') {
      return db_query("update ".TABLE_FAQS." set show_on_nonfaq = '0' where id = '" . (int)$faq_id . "'");
    } else {
      return - 1;
    }
  }

  /**
   * Change the favorite flag for a category.
   * The last_modified field is not updated because it affects the
   * newest FAQ display in the external FAQ box.
   *
   * @param mixed $cat_id
   * @param mixed $status
   * @return
   */
  function set_cat_favorite($cat_id, $status) {
    if ($status == '1') {
      return db_query("update ".TABLE_FAQCATS." set show_on_nonfaq = '1' where id = '" . (int)$cat_id . "'");
    } elseif ($status == '0') {
      return db_query("update ".TABLE_FAQCATS." set show_on_nonfaq = '0' where id = '" . (int)$cat_id . "'");
    } else {
      return - 1;
    }
  }

  /**
   * FaqAdmin::set_cat_status()
   *
   * @param mixed $categories_id
   * @param mixed $status
   * @return
   */
  function set_cat_status($categories_id, $status) {

    $category_data_set = array();
    $categories_query = db_query("SELECT id FROM ".TABLE_FAQCATS." WHERE parent_id = '" . $categories_id . "'");
    while ($category_data = db_fetch_array($categories_query)) {
      // get direct subcategory IDs
      $category_data_set[] = $category_data['id'];
      // get branch subcategory IDs
      $categories_query2 = db_query("SELECT id FROM ".TABLE_FAQCATS." WHERE parent_id = '" . $categories_id . "'");
      if (db_num_rows($categories_query2) > 0) {
        while ($category_data2 = db_fetch_array($categories_query2)) {
          $category_data_set[] = $category_data2['id'];
        }
      }
    }

    if ($status == '1') {
      db_query("update ".TABLE_FAQCATS." set category_status = '1', last_modified = now() where id = '" . (int)$categories_id . "'");
      for ($i = 0; $i < sizeof($category_data_set); $i++) {
        db_query("update ".TABLE_FAQCATS." set category_status = '1', last_modified = now() where id = '" . (int)$category_data_set[$i] . "'");
      }
      return 1;
    } elseif ($status == '0') {
      db_query("update ".TABLE_FAQCATS." set category_status = '0', last_modified = now() where id = '" . (int)$categories_id . "'");
      for ($i = 0; $i < sizeof($category_data_set); $i++) {
        db_query("update ".TABLE_FAQCATS." set category_status = '0', last_modified = now() where id = '" . (int)$category_data_set[$i] . "'");
      }
      return 1;
    } else {
      return - 1;
    }
  }

  /**
   * Primarily used as a callback function for array_map()
   * Merely a cast. Decimal accuracy and rounding is not important here.
   *
   * @param string a string to cast to an integer.
   * @return int cast a string to an integer
   */
  private static function string_to_int($string) {
    return (int)$string;
  }

  /**
   * FaqAdmin::parse_cat_path()
   *
   * @param mixed $fcPath
   * @return
   */
  function parse_cat_path($fcPath) {
    // make sure the faq IDs are integers
    //$fcPath_array = array_map('FaqAdmin::string_to_int', explode('_', $fcPath));

    $fcPath_array = explode('_', $fcPath);
    for($i=0; $i<count($fcPath_array); $i++){
      $fcPath_array[$i] = self::string_to_int($fcPath_array[$i]);
    }

    // make sure no duplicate faq IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($fcPath_array);
    for ($i = 0; $i < $n; $i++) {
      if (!in_array($fcPath_array[$i], $tmp_array)) {
        $tmp_array[] = $fcPath_array[$i];
      }
    }
    return $tmp_array;
  }

  /**
   * FaqAdmin::remove_cat()
   *
   * @param mixed $category_id
   * @return
   */
  function remove_cat($category_id) {
    db_query("delete from ".TABLE_FAQCATS." where id = '" . (int)$category_id . "'");
    db_query("delete from ".TABLE_FAQS2FAQCATS." where faqcategory_id = '" . (int)$category_id . "'");
  }

  /**
   * FaqAdmin::remove_faq()
   *
   * @param mixed $faq_id
   * @return
   */
  function remove_faq($faq_id) {
    db_query("delete from ".TABLE_FAQS." where id = '" . (int)$faq_id . "'");
    db_query("delete from ".TABLE_FAQS2FAQCATS." where faq_id = '" . (int)$faq_id . "'");
  }
}
?>