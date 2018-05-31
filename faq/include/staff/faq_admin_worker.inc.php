<?php
/* *************************************************************************
 Id: faq_admin_worker.inc.php

 Core FAQ administration functionality.


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

// get faq category path
if (isset($_GET['fcPath'])) {
  $fcPath = $_GET['fcPath'];
} else {
  $fcPath = 0;
}
if (FaqFuncs::not_null($fcPath)) {
  $fcPath_array = $faqAdmin->parse_cat_path($fcPath);
  $fcPath = implode('_', $fcPath_array);
  $current_faq_cat_id = $fcPath_array[(sizeof($fcPath_array) - 1)];
} else {
  $current_faq_cat_id = 0;
}


$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (FaqFuncs::not_null($action) && (OSFDB_STAFF_AS_ADMIN=='true' || $osf_isAdmin)) {
  switch ($action) {

    /// status flag
    case 'setflag':
      if (($_GET['flag'] == '0') || ($_GET['flag'] == '1')) {
        if (isset($_GET['fID'])) {
          $faqAdmin->set_status($_GET['fID'], $_GET['flag']);//faq status
        }
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('cID', 'action', 'flag')) ));
      break;
    case 'setflag_categories':
      if (($_GET['flag'] == '0') || ($_GET['flag'] == '1')) {
        if (isset($_GET['cID'])) {
          $faqAdmin->set_cat_status($_GET['cID'], $_GET['flag']);//cat status. also sets all child statuses
        }
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fID', 'action', 'flag')) ));
      break;

    /// featured flag
    case 'setfav':
      if (($_GET['flag'] == '0') || ($_GET['flag'] == '1')) {
        if (isset($_GET['fID'])) {
          $faqAdmin->set_favorite($_GET['fID'], $_GET['flag']);//faq featured
        }
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('cID', 'action', 'flag')) ));
      break;
    case 'setfav_categories':
      if (($_GET['flag'] == '0') || ($_GET['flag'] == '1')) {
        if (isset($_GET['cID'])) {
          $faqAdmin->set_cat_favorite($_GET['cID'], $_GET['flag']);//cat featured. doesn't affect child statuses
        }
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fID', 'action', 'flag')) ));
      break;


    case 'insert_category':
    case 'update_category':
      if (!FaqFuncs::not_null($categories_id)) $categories_id = db_input($_GET['cID'], false);

      $sql_data_array = array('category' => db_input($_POST['category'], false),
                              'category_status' => db_input($_POST['category_status'], false));

      if ($action == 'insert_category') {
        $insert_sql_data = array('parent_id' => $current_faq_cat_id,
                                 'date_added' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
        $sqle->db_compile(TABLE_FAQCATS, $sql_data_array);

        //print('<pre>');print_r($sql_data_array);print('</pre>');

        $categories_id = db_insert_id();
      } elseif ($action == 'update_category') {
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $update_sql_data);
        $sqle->db_compile(TABLE_FAQCATS, $sql_data_array, FaqSQLExt::$UPDATE, "id = '" . (int)$categories_id . "'");
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action')) . 'fcPath=' . $fcPath . '&cID=' . $categories_id));
      break;


    case 'insert_faq':
    case 'update_faq':
      if (isset($_GET['fID']))
      $faq_id = db_input($_GET['fID'], false);

      $sql_data_array = array('question' => strip_tags($_POST['question']),
                              'answer' => $_POST['answer'],
                              'faq_active' => $_POST['faq_active'],
                              'name' => $_POST['name'],
                              'email' => $_POST['email'],
                              'phone' => $_POST['phone']);

      if (isset($_POST['remove_pdf'])) {
        db_query("update " . TABLE_FAQS . " set pdfupload = '' where id = '" . $faq_id . "'");
      } elseif (isset($_POST['pdfupload']) && FaqFuncs::not_null($_POST['pdfupload']) && ($_POST['pdfupload'] != 'none')) {
        $sql_data_array['pdfupload'] = db_input($_POST['pdfupload'], false);
      }

      if ($action == 'insert_faq') {
        $insert_sql_data = array('date_added' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
        $sqle->db_compile(TABLE_FAQS, $sql_data_array);
        $faq_id = db_insert_id();
        db_query("insert into " . TABLE_FAQS2FAQCATS . " (faq_id, faqcategory_id) values ('" . (int)$faq_id . "', '" . (int)$current_faq_cat_id . "')");
      } elseif ($action == 'update_faq') {
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $update_sql_data);
        $sqle->db_compile(TABLE_FAQS, $sql_data_array, FaqSQLExt::$UPDATE, "id = '" . (int)$faq_id . "'");
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action')) . 'fcPath=' . $fcPath . '&fID=' . (int)$faq_id));
      break;


    case 'delete_category_confirm':
      if (isset($_POST['cat_id'])) {
        $cat_id = db_input($_POST['cat_id'], false);
        $categories = $faqAdmin->get_tree($cat_id, '', '0', '', true);
        $faqs = array();
        $faqs_delete = array();
        for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
          $faq_ids_query = db_query("select faqcategory_id from " . TABLE_FAQS2FAQCATS . " where faqcategory_id = '" . (int)$categories[$i]['id'] . "'");
          while ($faq_ids = db_fetch_array($faq_ids_query)) {
            $faqs[$faq_ids['id']]['categories'][] = $categories[$i]['id'];
          }
        }
        reset($faqs);
        while (list($key, $value) = each($faqs)) {
          $category_ids = '';
          for ($i = 0, $n = sizeof($value['categories']); $i < $n; $i++) {
            $category_ids .= "'" . (int)$value['categories'][$i] . "', ";
          }
          $category_ids = substr($category_ids, 0, -2);
          $check_query = db_query("select count(*) as total from " . TABLE_FAQS2FAQCATS . " where faq_id = '" . (int)$key . "' and faqcategory_id not in (" . $category_ids . ")");
          $check = db_fetch_array($check_query);
          if ($check['total'] < '1') {
            $faqs_delete[$key] = $key;
          }
        }
        // removing categories can be a lengthy process
        set_time_limit(0);
        for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
          $faqAdmin->remove_cat($categories[$i]['id']);
        }
        reset($faqs_delete);
        while (list($key) = each($faqs_delete)) {
          $faqAdmin->remove_faq($key);
        }
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action')) . 'fcPath=' . $fcPath));
      break;


    case 'delete_faq_confirm':
      if (isset($_POST['faq_id']) && isset($_POST['faq_categories']) && is_array($_POST['faq_categories'])) {
        $faq_id = db_input($_POST['faq_id'], false);
        $faq_categories = $_POST['faq_categories'];
        for ($i = 0, $n = sizeof($faq_categories); $i < $n; $i++) {
          db_query("delete from " . TABLE_FAQS2FAQCATS . " where faq_id = '" . (int)$faq_id . "' and faqcategory_id = '" . (int)$faq_categories[$i] . "'");
        }
        $faq_cats_query = db_query("select count(*) as total from " . TABLE_FAQS2FAQCATS . " where faq_id = '" . (int)$faq_id . "'");
        $faq_cats = db_fetch_array($faq_cats_query);
        if ($faq_cats['total'] == 0) {
          $faqAdmin->remove_faq($faq_id);
        }
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action')) . 'fcPath=' . $fcPath));
      break;


    case 'move_cat_confirm':
      if (isset($_POST['cat_id']) && ($_POST['cat_id'] != $_POST['move_to_cat_id'])) {
        $cat_id = db_input($_POST['cat_id'], false);
        $new_parent_id = db_input($_POST['move_to_cat_id'], false);
        $path = explode('_', $faqAdmin->get_generated_cat_ids($new_parent_id));
        if (in_array($cat_id, $path)) {
          $messageHandler->addNext(OSF_ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');
          FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, 'fcPath=' . $fcPath . '&cID=' . $cat_id));
        } else {
          db_query("update " . TABLE_FAQCATS . " set parent_id = '" . (int)$new_parent_id . "', last_modified = now() where id = '" . (int)$cat_id . "'");
          $messageHandler->addNext('Category Moved', 'success');
          FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action')) . 'fcPath=' . $new_parent_id . '&cID=' . $cat_id));
        }
      }
      break;


    case 'move_faq_confirm':
      $faq_id = db_input($_POST['faq_id'], false);
      $new_parent_id = db_input($_POST['move_to_cat_id'], false);
      $duplicate_check_query = db_query("select count(*) as total from " . TABLE_FAQS2FAQCATS . " where faq_id = '" . (int)$faq_id . "' and faqcategory_id = '" . (int)$new_parent_id . "'");
      $duplicate_check = db_fetch_array($duplicate_check_query);
      if ($duplicate_check['total'] < 1)
      db_query("update " . TABLE_FAQS2FAQCATS . " set faqcategory_id = '" . (int)$new_parent_id . "' where faq_id = '" . (int)$faq_id . "' and faqcategory_id = '" . (int)$current_faq_cat_id . "'");

      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action')) . 'fcPath=' . $new_parent_id . '&fID=' . $faq_id));
      break;


    /// only applies to FAQs
    case 'copy_to_confirm':
      if (isset($_POST['faq_id']) && isset($_POST['cat_id'])) {
        $faq_id = db_input($_POST['faq_id'], false);
        $cat_id = db_input($_POST['cat_id'], false);

        if ($_POST['copy_as'] == 'link') {
          if ($cat_id != $current_faq_cat_id) {
            $check_query = db_query("select count(*) as total from " . TABLE_FAQS2FAQCATS . " where faq_id = '" . (int)$faq_id . "' and faqcategory_id = '" . (int)$cat_id . "'");
            $check = db_fetch_array($check_query);
            if ($check['total'] < '1') {
              db_query("insert into " . TABLE_FAQS2FAQCATS . " (faq_id, faqcategory_id) values ('" . (int)$faq_id . "', '" . (int)$cat_id . "')");
            }
          } else {
            $messageHandler->addNext(OSF_ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
          }
        } elseif ($_POST['copy_as'] == 'duplicate') {
          $faq_query = db_query("select question, answer, faq_active, date_added, last_modified, name, email, phone, pdfupload from " . TABLE_FAQS . " where id = '" . (int)$faq_id . "'");
          $faq = db_fetch_array($faq_query);

          $sqle->db_compile(TABLE_FAQS, $faq, FaqSQLExt::$INSERT);
          $dup_faq_id = db_insert_id();

          db_query("insert into " . TABLE_FAQS2FAQCATS . " (faq_id, faqcategory_id) values ('" . (int)$dup_faq_id . "', '" . (int)$cat_id . "')");
          $faq_id = $dup_faq_id;
        }
      }
      FaqFuncs::redirect(FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action')) . 'fcPath=' . $cat_id . '&fID=' . $faq_id));
      break;
  }
}
?>