<?php
/* *************************************************************************
  Id: faq_external.php

  Client side external FAQ display box.

  Faq'n Faq box.
  This box can be used in osTicket pages and will display FAQs & FaqCats
  that are set as featured by admin, newest and the most popular (by view).
  Quantity limits and display options are set on the admin_settings page.

  TO USE:
  <?php require(ROOT_DIR.'faq/include/client/faq_external.php'); ?>


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

/// CONFIG
require_once(ROOT_PATH . 'faq/include/main.faq.php'); // !important
require_once(DIR_FAQ_INCLUDES . 'FaqExternal.php');// static

echo '<style type="text/css" media="all">body{direction:' . OSF_LANG_DIRECTION . '; unicode-bidi:embed;}</style>';

/// only display if this feature is enabled in admin
if(OSFDB_DISABLE_CLIENT=='false' && OSFDB_EXT_FAQS_ALLOW=='true'){

  /// DEFAULT LANGUAGE FILE.
  require_once (DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/faq_external.lang.php');

  require_once(DIR_FAQ_INCLUDES . 'FaqFuncs.php');


  /* featured */
  /// categories
  $featured_cats_res = db_query("SELECT f1.id, f1.category, (SELECT COUNT(f2fc.faq_id) FROM ".TABLE_FAQS2FAQCATS." f2fc, ".TABLE_FAQCATS." fc, ".TABLE_FAQS." f WHERE f1.id = fc.id AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = 1) AS fcount FROM ".TABLE_FAQCATS." f1 WHERE f1.category_status = 1 AND f1.show_on_nonfaq = 1 order by RAND() LIMIT 0,".(int)OSFDB_EXT_LIMIT.";");

  /// faqs
  $featured_faqs_res = db_query("SELECT distinct f.id, f2f.faqcategory_id as cid, f.question FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f left join ".TABLE_FAQCATS." fc on(f2f.faqcategory_id=fc.id) WHERE ((f2f.faqcategory_id = 0) OR (f2f.faqcategory_id = fc.id AND fc.category_status = 1)) AND f2f.faq_id = f.id AND f.faq_active = 1 AND f.show_on_nonfaq = 1 group by f.question order by RAND() LIMIT 0,".(int)OSFDB_EXT_LIMIT.";");



  /* popular */
  /// categories
  $popular_cats_res = db_query("SELECT f1.id, f1.category, (SELECT COUNT(f2fc.faq_id) FROM ".TABLE_FAQS2FAQCATS." f2fc, ".TABLE_FAQCATS." fc, ".TABLE_FAQS." f WHERE f1.id = fc.id AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = 1) AS fcount FROM ".TABLE_FAQCATS." f1 WHERE f1.category_status = 1 and f1.client_views > 0 order by f1.client_views DESC, f1.category ASC LIMIT 0,".(int)OSFDB_EXT_LIMIT.";");

  /// faqs
  $popular_faqs_res = db_query("SELECT distinct f.id, f2f.faqcategory_id as cid, f.question FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f left join ".TABLE_FAQCATS." fc on(f2f.faqcategory_id=fc.id) WHERE ((f2f.faqcategory_id = 0) OR (f2f.faqcategory_id = fc.id AND fc.category_status = 1)) AND f2f.faq_id = f.id AND f.faq_active = 1 AND f.client_views > 0 group by f.question order by f.client_views DESC, f.question ASC LIMIT 0,".(int)OSFDB_EXT_LIMIT.";");



  /* new */
  /// categories
  $new_cats_res = db_query("SELECT f1.id, f1.category, IFNULL(f1.last_modified, f1.date_added) as sort_date, (SELECT COUNT(f2fc.faq_id) FROM ".TABLE_FAQS2FAQCATS." f2fc, ".TABLE_FAQCATS." fc, ".TABLE_FAQS." f WHERE f1.id = fc.id AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = 1) AS fcount FROM ".TABLE_FAQCATS." f1 WHERE f1.category_status = 1 order by sort_date DESC, f1.category LIMIT 0,".(int)OSFDB_EXT_LIMIT.";");

  /// faqs
  $new_faqs_res = db_query("SELECT distinct f.id, f2f.faqcategory_id as cid, f.question, IFNULL(f.last_modified, f.date_added) as sort_date FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f left join ".TABLE_FAQCATS." fc on(f2f.faqcategory_id=fc.id) WHERE ((f2f.faqcategory_id = 0) OR (f2f.faqcategory_id = fc.id AND fc.category_status = 1)) AND f2f.faq_id = f.id AND f.faq_active = 1 group by f.question order by sort_date DESC, f.question ASC LIMIT 0,".(int)OSFDB_EXT_LIMIT.";");



/// PAGE OUTPUT
?>
<div id="faq_box">
<?php
  if(OSFDB_EXT_FEATURED=='true' && (db_num_rows($featured_cats_res) > 0 || db_num_rows($featured_faqs_res) > 0)){
?>
  <div class="featured">
    <h3><?php echo OSF_FEATURED; ?></h3>
<?php
    if(db_num_rows($featured_cats_res)>0){
      echo '<h4>' . OSF_FAQCATS . '</h4>';
      FaqExternal::list_ext_faqcats($featured_cats_res);
    }
    if(db_num_rows($featured_faqs_res)>0){
      echo '<h4>' . OSF_FAQS . '</h4>';
      FaqExternal::list_ext_faqs($featured_faqs_res);
    }
?>
  </div>
<?php
  }


  if(OSFDB_EXT_POPULAR=='true' && (db_num_rows($popular_cats_res) > 0 || db_num_rows($popular_faqs_res) > 0)){
?>
  <div class="popular">
    <h3><?php echo OSF_POPULAR; ?></h3>
<?php
    if(db_num_rows($popular_cats_res)>0){
      echo '<h4>' . OSF_FAQCATS . '</h4>';
      FaqExternal::list_ext_faqcats($popular_cats_res);
    }
    if(db_num_rows($popular_faqs_res)>0){
      echo '<h4>' . OSF_FAQS . '</h4>';
      FaqExternal::list_ext_faqs($popular_faqs_res);
    }
?>
  </div>
<?php
  }


  if(OSFDB_EXT_NEW=='true' && (db_num_rows($new_cats_res) > 0 || db_num_rows($new_faqs_res) > 0)){
?>
  <div class="newest">
    <h3><?php echo OSF_NEW; ?></h3>
<?php
    if(db_num_rows($new_cats_res)>0){
      echo '<h4>' . OSF_FAQCATS . '</h4>';
      FaqExternal::list_ext_faqcats($new_cats_res);
    }
    if(db_num_rows($new_faqs_res)>0){
      echo '<h4>' . OSF_FAQS . '</h4>';
      FaqExternal::list_ext_faqs($new_faqs_res);
    }
?>
  </div>
<?php
  }
?>
</div>
<?php
} //if(OSFDB_DISABLE_CLIENT==='false' && OSFDB_EXT_FAQS_ALLOW=='true'){
?>