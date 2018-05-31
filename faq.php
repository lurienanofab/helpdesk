<?php
/* *************************************************************************
  Id: faq.php

  Client side FAQ display page.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

require ('./faq/include/accelerator.faq.php'); // page accelerator. MUST BE FIRST

require ('client.inc.php');//osTicket file
require (ROOT_PATH . 'faq/include/main.faq.php'); // !important

/// DEFAULT LANGUAGE FILE.
require_once (DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/faq.lang.php');

require (DIR_FAQ_INCLUDES . 'FaqFuncs.php');

require_once (DIR_FAQ_INCLUDES . 'FaqPaginator.php');
require_once (DIR_FAQ_INCLUDES . 'FaqCrumb.php');
require_once (DIR_FAQ_INCLUDES . 'FaqSQLExt.php');


/// Bail out if the client side was disabled by admin
if(OSFDB_DISABLE_CLIENT=='true'){
  require(CLIENTINC_DIR.'header.inc.php');
  echo $osf_langDirection;
  echo '<p>' . OSF_CLIENT_DISABLED . '</p>';
  require(CLIENTINC_DIR.'footer.inc.php');
  exit(0);
}

/// INTERNAL PHP
$pages = new FaqPaginator(FILE_FAQ);
$sqle = new FaqSQLExt;
$FaqCrumb = new FaqCrumb;


/* TODO: make search a bit smarter
 *
 * SEARCH IDEAS:
 * split multiple words
 * use additional MySql SOUNDEX in conditions.
 * Also look into updating the keyword highlighting to support these ideas.
 */

// Make sure we are not returning results from html formatting.
// EG: only get results from visible text
$search_str = isset($_GET['faqsearch']) ? db_input(htmlspecialchars(trim($_GET['faqsearch'])), false) : '';

$search_desc = isset($_GET['search_desc']) ? true : false;
$showall = ($_GET['print']=='true') ? true : false;
$answer = isset($_GET['answer']) ? (int)$_GET['answer'] : 0;

if(isset($_GET['cid'])){
  $category_id = (int)$_GET['cid'];

  // update cat view counter here
  if(!isset($_GET['answer']) && $category_id > 0){
    db_query('UPDATE ' . TABLE_FAQCATS . ' SET client_views = (client_views + 1) WHERE id = ' . $category_id . ';');
  }

}else{
  $category_id = 0;
}

if($category_id > 0){
  $cat_name = FaqFuncs::get_cat_name($category_id);
}else{
  $cat_name = OSF_TOP_LEVEL;
}



if($search_str!='') {

  /// faq pagination count
  $faq_count_query = db_query("SELECT distinct f.id FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f left join ".TABLE_FAQCATS." fc on(f2f.faqcategory_id=fc.id) WHERE ((f2f.faqcategory_id = 0) OR (f2f.faqcategory_id = fc.id and fc.category_status = '1')) and f2f.faq_id = f.id and f.faq_active = '1' and (f.question LIKE '%".$search_str."%'".($search_desc ? " OR f.answer LIKE '%".$search_str."%')" : ')')." group by f.id order by f.question");

  $pages->items_total = db_num_rows($faq_count_query);
  $pages->paginate();

  /// categories search
  $result_subcats = db_query("SELECT distinct f1.id, f1.category, (SELECT COUNT(f2fc.faq_id) FROM ".TABLE_FAQS2FAQCATS." f2fc, ".TABLE_FAQCATS." fc, ".TABLE_FAQS." f WHERE f1.id = fc.id AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = '1') AS fcount FROM ".TABLE_FAQCATS." f1 WHERE f1.category LIKE '%".$search_str."%' AND f1.category_status = '1' group by f1.id order by f1.category");

  /// faq search
  $result_faqs = db_query("SELECT distinct f.id, f.question, f.answer, f.name, f.date_added, f.last_modified, f.pdfupload FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f left join ".TABLE_FAQCATS." fc on(f2f.faqcategory_id=fc.id) WHERE ((f2f.faqcategory_id = 0) OR (f2f.faqcategory_id = fc.id and fc.category_status = '1')) and f2f.faq_id = f.id and f.faq_active = '1' and (f.question LIKE '%".$search_str."%'".($search_desc ? " OR f.answer LIKE '%".$search_str."%')" : ')')." group by f.id order by f.question" . (($pages->items_total > 0) ? " " . $pages->limit : ""));

} else {

  /// faq pagination count
  if($category_id==0){
    $faq_condition = "f2f.faq_id = f.id and f2f.faqcategory_id = ".$category_id." and f.faq_active = 1";
    $faq_count_query = db_query("SELECT distinct COUNT(f.id) as faq_count FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f WHERE " . $faq_condition . " order by f.question");
  }else{
    $faq_condition = "f2f.faqcategory_id = fc.id and f2f.faq_id = f.id and f2f.faqcategory_id = ".$category_id." and fc.category_status = 1 and f.faq_active = 1";
    $faq_count_query = db_query("SELECT distinct COUNT(f.id) as faq_count FROM ".TABLE_FAQS." f, ".TABLE_FAQCATS." fc, ".TABLE_FAQS2FAQCATS." f2f WHERE " . $faq_condition . " order by f.question");
  }

  $row_count = db_fetch_array($faq_count_query);

  $pages->set_items_total((int)$row_count['faq_count']);
  /* **************************************************
   * START pagination control for remote requests.
   ************************************************** */
  if(!isset($_GET['pg']) && !isset($_GET['page']) && $answer>0){
    $pgConfigQuery = $sqle->db_compile(TABLE_FAQS." f, ".TABLE_FAQCATS." fc, ".TABLE_FAQS2FAQCATS." f2f"
                                      ,array('distinct f.id')
                                      ,FaqSQLExt::$SELECT
                                      ,$faq_condition
                                      ,''
                                      ,'f.question');

    $pgCount = 0;
    while ($pgConfig = db_fetch_array($pgConfigQuery)) {
      $pgCount++;
      if($answer==$pgConfig['id']) break;
    }

    if($pgCount > 0){
      $_GET['pg'] = ceil($pgCount/$pages->items_per_page);
    }
    //print('$pgCount='.$pgCount.', $pages->items_per_page='.$pages->items_per_page.', $_GET[\'pg\']='.$_GET['pg']);
  }
  /* **************************************************
   * END pagination control for remote requests.
   ************************************************** */
  $pages->paginate();


  /// subcategories
  $result_subcats = db_query("SELECT distinct f1.id, f1.category, (SELECT COUNT(f2fc.faq_id) FROM ".TABLE_FAQS2FAQCATS." f2fc, ".TABLE_FAQCATS." fc, ".TABLE_FAQS." f WHERE f1.id = fc.id AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = 1) AS fcount FROM ".TABLE_FAQCATS." f1 WHERE f1.parent_id = '".$category_id."' AND f1.category_status = '1' order by f1.category");
  /// faqs
  if($category_id==0){
    $result_faqs = db_query("SELECT distinct f.id, f.question, f.answer, f.name, f.date_added, f.last_modified, f.pdfupload FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f WHERE " . $faq_condition . " order by f.question" . (($pages->items_total > 0) ? " " . $pages->limit : ""));
  }else{
    $result_faqs = db_query("SELECT distinct f.id, f.question, f.answer, f.name, f.date_added, f.last_modified, f.pdfupload FROM ".TABLE_FAQS." f, ".TABLE_FAQCATS." fc, ".TABLE_FAQS2FAQCATS." f2f WHERE " . $faq_condition . " order by f.question" . (($pages->items_total > 0) ? " " . $pages->limit : ""));
  }
}
$rf_rows = db_num_rows($result_faqs);






$FaqCrumb->add(OSF_LINK, FaqFuncs::format_url(FILE_FAQ, '', 'SSL'));

if($search_str!='') {
	$FaqCrumb->add(OSF_SEARCH_LINK.$search_str, FaqFuncs::format_url(FILE_FAQ, ($showall ? 'print=true&':'').'faqsearch='.$search_str.($search_desc ? '&search_desc=true' : ''), 'SSL'));
}else{

	/// collect parent category data
	if(isset($_GET['cid']) && is_numeric($_GET['cid']) && $_GET['cid'] > 0){
		$parent_cats_array = FaqFuncs::faq_get_parent_tree((int)$_GET['cid']);// !important - also required for page output

		if(is_array($parent_cats_array)){
			for ($i=0; $i<sizeof($parent_cats_array); $i++) {
				$FaqCrumb->add($parent_cats_array[$i]['title'], $parent_cats_array[$i]['link'] . ($showall ? '&print=true':''));
			}
		}
		$FaqCrumb->add($cat_name, FaqFuncs::format_url(FILE_FAQ, 'cid='.$category_id.($showall ? '&print=true':''), 'SSL', $cat_name));
	}
}

$rssAlt = ((OSFDB_FEED_ATOM=='true') ? 'Atom':'RSS') . ' Feed';








/// PAGE OUTPUT
require(CLIENTINC_DIR.'header.inc.php');
echo $osf_langDirection;
?>
<div id="faqs">

  <?php echo $FaqCrumb->get(' &raquo; '); ?>
  <div class="clear"></div>

  <div style="float:left;">
    <a name="top"></a><h1>
    <?php if(OSFDB_FEED_ALLOW=='true'){ ?><a href="<?php echo FILE_FAQ_FEED; ?>" target="_blank"><img src="faq/img/icons/feed-icon-14x14.png" border="0" alt="<?php echo $rssAlt; ?>" title="<?php echo $rssAlt; ?>" /></a> <?php } ?>
    <?php echo OSF_TITLE; ?></h1>
<?php
if(OSFDB_USER_SUBMITS_ALLOW=='true'){
  if(OSFDB_USER_ANON=='true' || $osf_isClient){
    echo ' <a href="'.FaqFuncs::format_url(FILE_FAQ_SUBMIT, '', 'SSL').'">' . OSF_SUGGEST_NEW . '</a>';
  }
}
?>
  </div>
	<div style="float:right;">
    <form action="<?php echo FaqFuncs::format_url(FILE_FAQ, '', 'SSL'); ?>" method="get" name="faq_search" id="faq_search" style="padding:0;margin:0;">
      <input type="hidden" name="print" value="true" />
      <input type="text" name="faqsearch"<?php echo (isset($_GET['faqsearch']) ? ' value="'.trim($_GET['faqsearch']).'"' : ' value="'.OSF_SEARCH_FIELD.'"'); ?> style="width:170px;" onfocus="if(this.value=='<?php echo OSF_SEARCH_FIELD; ?>'){this.value='';}" onblur="if(this.value==''){this.value='<?php echo OSF_SEARCH_FIELD; ?>';}" /><br />
      <input type="submit" class="cssbuttonsubmit" value="<?php echo OSF_SEARCH_BTN; ?>" /> <input type="checkbox" name="search_desc" id="search_desc" checked="checked" /> <small><?php echo OSF_SEARCH_ANSWER; ?></small>
    </form>
	</div>
  <div class="clear"></div>

  <hr />



<?php
/// output root category menu
if((!isset($_GET['cid']) || $category_id==0) && !FaqFuncs::not_null($search_str)){

  echo '  <h4>' . OSF_SELECT_CAT . '</h4>' . "\n";

  $faq_tree_array = FaqFuncs::faq_get_tree();
  $ift=0;
  for ($ift=0; $ift<sizeof($faq_tree_array); $ift++) {
    echo $faq_tree_array[$ift]['text'] . '<br />' . "\n";
  }

  if($category_id>0 && $ift==0){
    echo '  <h3>'.OSF_NO_FAQS.'</h3>';
  }
}



/// output parent categories
if($category_id>0){
  echo '  <h2 class="fade">'.OSF_PARENT_CATS.'</h2>' . "\n";
?>
  <div>
    &bull; <a href="<?php echo FaqFuncs::format_url(FILE_FAQ, '', 'SSL'); ?>" class="faq"><?php echo OSF_ALL_CATS; ?></a><br />
<?php
  if(is_array($parent_cats_array)){
  	for ($i=0; $i<sizeof($parent_cats_array); $i++) {
        echo $parent_cats_array[$i]['text'] . '<br />' . "\n";
  	}
  }
?>
  </div>
  <div class="clear"></div>
<?php
}



/// output subcategories
if($category_id>0 && db_num_rows($result_subcats) > 0){
  $subcat_cnt = 0;
?>
  <br />
  <?php echo '  <h2 class="fade">'.(FaqFuncs::not_null($search_str) ? OSF_CATS : OSF_SUB_CATS).'</h2>' . "\n"; ?>
  <div>
<?php
  while ($subcats = db_fetch_array($result_subcats)) {
?>
    &bull; <a href="<?php echo FaqFuncs::format_url(FILE_FAQ, FaqFuncs::get_all_get_params(array('cid','i','ipp','faqsearch','search_desc')) . 'cid='.$subcats['id'], 'SSL', $subcats['category']); ?>" class="faq"><?php echo FaqFuncs::highlight_keywords($subcats['category'], $_GET['faqsearch']); ?></a><?php echo ' (' . $subcats['fcount'] . ')'; ?><br />
<?php
  }
?>
  </div>
  <div class="clear"></div>
<?php
}





///////////////////////////////////////
///////////////////////////////////////
if($rf_rows > 0){
?>
  <br />
<?php
  echo '<h2>' .(isset($_GET['faqsearch']) ? OSF_SEARCH_RESULTS . ' ('.$pages->items_total.')' : '<span class="fade">'.OSF_FAQ_NAME.'</span>' . $cat_name). '</h2>' . "\n";


  /// Pagination
  switch(OSFDB_CLIENT_PG_STRIP){
    case '2':
    // bottom only
    break;

    case '1':
    case '3':
    default:
      //if($pages->items_total > $rf_rows){
        echo '<div class="paginate_row">';
        echo $pages->display_pages(); // page numbers
        echo $pages->display_jump_menu(); // page jump menu
        echo $pages->display_items_per_page(); // items per page menu
        echo '</div>' . "\n";
      //}
    break;
  }
?>
    <table width="100%" border="0" cellpadding="8" cellspacing="0">
<?php
  /// output faq data
  while ($faqs = db_fetch_array($result_faqs)) {

    //display the authors name
    $osf_question = $faqs['answer'];
    if(FaqFuncs::not_null($faqs['name'])){
      $osf_question .= '<br /><small class="fade"><b>' . OSF_FAQ_AUTHOR . '</b> ' . $faqs['name'] . '</small>';
    }

    /// selected faq
    if ($answer==(int)$faqs['id'] || $showall || $rf_rows==1) {

      // update faq view counter
      db_query('UPDATE ' . TABLE_FAQS . ' SET client_views = (client_views + 1) WHERE id = ' . $faqs['id'] . ';');
?>
      <tr>
        <td class="Q" valign="top"><?php echo OSF_Q; ?></td>
        <td valign="bottom" class="question"><a name="f<?php echo $faqs['id']; ?>"></a>
          <p>
<?php
      if($rf_rows == 1 || $showall){
        // if theres only one FAQ or $showall==true we don't add any FAQ link.
        echo FaqFuncs::highlight_keywords($faqs['question'], $search_str);
      }else{
?>
            <a rel="nofollow" href="<?php echo FaqFuncs::format_url(FILE_FAQ, FaqFuncs::get_all_get_params(array('cid','answer','i','ipp')) . 'cid='.$category_id, 'SSL', $faqs['question']) . '#f'.$faqs['id']; ?>"><?php echo FaqFuncs::highlight_keywords($faqs['question'], $search_str); ?></a>
<?php
      }
?>
          </p>
        </td>
      </tr>
      <tr>
        <td class="A" valign="top"><?php echo OSF_A; ?></td>
        <td valign="bottom"><?php echo isset($_GET['search_desc']) ? FaqFuncs::highlight_keywords($osf_question, $search_str) : $osf_question; ?>
<?php
      if (!empty($faqs['pdfupload'])) {
        $upext = substr($faqs['pdfupload'], strrpos($faqs['pdfupload'], '.') +1);

        if($upext == 'pdf'){
          echo '<hr />' . OSF_PDF_DOWNLOAD;
        }else{
          echo '<hr />' . OSF_DOC_DOWNLOAD;
        }

        echo ' <a href="' . DIR_WS_DOC . $faqs['pdfupload'] . '" target="_blank">' .$faqs['pdfupload']. '</a> (' .FaqFuncs::display_filesize(filesize(DIR_FS_DOC . $faqs['pdfupload'])) . ')';

        if($upext == 'pdf'){
          echo OSF_ADOBE_READER;
        }
      }
?>
        </td>
      </tr>
<?php
    /// not selected faq
    } else {
?>
      <tr>
        <td class="Q" valign="top"><?php echo OSF_Q; ?></td>
        <td valign="bottom" class="question"><a name="f<?php echo $faqs['id']; ?>"></a><p><a href="<?php echo FaqFuncs::format_url(FILE_FAQ, FaqFuncs::get_all_get_params(array('cid','answer','i','ipp')) . 'cid='.$category_id.'&answer='.$faqs['id'], 'SSL', $faqs['question']) . '#f'.$faqs['id']; ?>" rel="nofollow"><?php echo FaqFuncs::highlight_keywords($faqs['question'], $_GET['faqsearch']); ?></a></p></td>
      </tr>
<?php
    }
  }///while
?>
    </table>
<?php
  /// Pagination
  switch(OSFDB_CLIENT_PG_STRIP){
    case '1':
    // top only
    break;

    case '2':
    case '3':
    default:
      //if($pages->items_total > $rf_rows){
        echo '<div class="paginate_row">';
        echo $pages->display_pages(); // page numbers
        echo $pages->display_jump_menu(); // page jump menu
        echo $pages->display_items_per_page(); // items per page menu
        echo '</div>' . "\n";
      //}
    break;
  }
?>
    <div class="paginate_row">
      <?php if($rf_rows > 1){ ?><a rel="nofollow" href="<?php echo FaqFuncs::format_url(FILE_FAQ, FaqFuncs::get_all_get_params(array('cid','print','i','ipp')) . 'cid='.$category_id.'&print='.(!$showall ? 'true' : 'false'), 'SSL', $cat_name) . '#top'; ?>"><?php echo (!$showall ? OSF_SHOW : OSF_HIDE) . OSF_ALL_ANSWERS; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<?php } ?>
      <a rel="nofollow" href="<?php echo FaqFuncs::format_url(FILE_FAQ, FaqFuncs::get_all_get_params(array('i','ipp')), 'SSL') . '#top'; ?>"><?php echo OSF_TOP; ?></a>
      &nbsp;&nbsp;|&nbsp;&nbsp;<a rel="nofollow" href="javascript:history.back()"><?php echo OSF_BACK; ?></a>
      <?php if(OSFDB_USER_SUBMITS_ALLOW=='true' && (OSFDB_USER_ANON=='true' || $osf_isClient)){ echo '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.FaqFuncs::format_url(FILE_FAQ_SUBMIT, '', 'SSL').'">' . OSF_SUGGEST_NEW . '</a>'; } ?>
      <?php if(OSFDB_FEED_ALLOW=='true'){ ?>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo FILE_FAQ_FEED; ?>" target="_blank"><img src="faq/img/icons/feed-icon-14x14.png" border="0" alt="<?php echo $rssAlt; ?>" title="<?php echo $rssAlt; ?>" /></a><?php } ?>
    </div>
    <?php if($rf_rows > 1 && !$showall){ ?><div class="clear"></div><div style="float:right;font-size:11px;" class="fade"> <?php echo OSF_REVEAL_HIDE; ?></div><?php } ?>

<?php
}elseif($category_id>0 || FaqFuncs::not_null($search_str)){
  echo '  <h3>'.OSF_NO_FAQS.'</h3>' . "\n";
}

if(FaqFuncs::not_null(OSFDB_OPTIONAL_FOOTER)) echo '<div class="clear"></div><hr />' . OSFDB_OPTIONAL_FOOTER . "\n";
?>
</div>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>