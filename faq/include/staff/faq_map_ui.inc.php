<?php
/* *************************************************************************
 Id: faq_map_ui.inc.php

 xml Sitemap User Interface.
 This allows the user to set a few options and generate a sitemap file.


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

/// DEFAULT LANGUAGE FILE.
require_once (DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/faq_map.lang.php');

require (DIR_FAQ_INCLUDES . 'FaqSeo.php');
require_once (DIR_FAQ_INCLUDES . 'FaqFeedSM.php');//need to access static vars
$seoFunc = new FaqSeo();


$osfDBRes = $sqle->db_compile(TABLE_FAQ_ADMIN, array('key_name','key_value'), FaqSQLExt::$SELECT, "key_name like 'OSFA_SM_%'");
while($osfDBArray = db_fetch_array($osfDBRes)){
  switch($osfDBArray['key_name']){
    case 'OSFA_SM_IDX':
      $sm_index = $osfDBArray['key_value'];
      break;
    case 'OSFA_SM_IDX_MAPS':
      $sm_idx_maps = $osfDBArray['key_value'];
      break;
    case 'OSFA_SM_MAP':
      $sm_map = $osfDBArray['key_value'];
      break;
    case 'OSFA_SM_NOTIFY':
      $sm_notify = $osfDBArray['key_value'];
      break;
    case 'OSFA_SM_PATH':
      $sm_filepath = $osfDBArray['key_value'];
      break;
    case 'OSFA_SM_TYPE':
      $sm_result = $osfDBArray['key_value'];
      break;
  }
}


$count_faqs_query = db_query("SELECT count(id) as id_count FROM " . TABLE_FAQCATS . " WHERE category_status=1;");
$count_faqs = db_fetch_array($count_faqs_query);
$map_entry_count = 2;// 1 for osticket page, 1 for osfaq page
$map_entry_count += $count_faqs['id_count'];
$sm_result = isset($sm_result) ? $sm_result : 'test';


/// set any pending messages
if(isset($_GET['result'])){
  $smResults = array('local','other','auto','unknown');

  $sm_result = in_array($_GET['result'], $smResults) ? $_GET['result'] : 'test';
  $sm_filename = $_GET['f'];
  $sm_url = $seoFunc->getPublicUrl($sm_filename);
  $sm_filename = basename($sm_url);
  $sm_filepath = str_ireplace(array(HTTP_SERVER, $sm_filename), '', $sm_url);


  if($sm_result=='local'){
    $messageHandler->add(sprintf(OSF_SITEMAP_SUCCESS, $sm_url), FaqMessage::$success);
  }elseif($sm_result=='other'){
    $messageHandler->add(sprintf(OSF_SITEMAP_SUCCESS_GZ, $sm_url), FaqMessage::$success);
  }elseif($sm_result=='auto'){
    $messageHandler->add(sprintf(OSF_SITEMAP_SUCCESS_IDX, $sm_url), FaqMessage::$success);
  }elseif($sm_result=='unknown'){
    $messageHandler->add(sprintf(OSF_SITEMAP_ERROR, $sm_url), FaqMessage::$error);
  }
}elseif(isset($_GET['smap'])){
  $sm_result = in_array($_GET['smap'], array('local','other','test')) ? db_input($_GET['smap'], false) : 'test';

}


// sane limits for sitemap output types
if($map_entry_count <= 500){
  $output_type = 'norm';
  $smname = 'sitemap.xml';
}elseif($map_entry_count > 500 && $map_entry_count < FaqFeedSM::$MAX_ENTRYS){
  $output_type = 'gzip';
  $smname = 'sitemap.xml.gz';
}elseif($map_entry_count >= FaqFeedSM::$MAX_ENTRYS){
  $output_type = 'auto';
  $smname = 'sitemap_index.xml';
}
//$output_type = 'auto';
//$smname = 'sitemap_index.xml';

$sm_filename = empty($sm_filename) ? $smname : $sm_filename;
$sm_filepath = empty($sm_filepath) ? '/' : $sm_filepath;
$sm_url = empty($sm_url) ? $seoFunc->getPublicUrl(OSF_DOC_ROOT . $sm_filepath . $sm_filename) : $sm_url;

$smap_vals = array();
$smap_vals[] = array('id' => 'local', 'text' => OSF_SITEMAP_LOCAL);
$smap_vals[] = array('id' => 'other', 'text' => OSF_SITEMAP_OTHER);
$smap_vals[] = array('id' => 'test', 'text' => OSF_SITEMAP_TEST);

// these options can be time consuming to run so we only call them when needed.
if($sm_result=='other'){
  $smappend_set = $seoFunc->findSitemapFiles(OSF_DOC_ROOT);
}elseif($sm_result=='local'){
  $smbase_vals = array();
  if(DIR_FS_WEB_ROOT != '/'){
    $smbase_vals[] = array('id' => '/', 'text' => '/' . $smname . $seoFunc->getFileState(OSF_DOC_ROOT.'/'.$smname));
  }
  $smbase_vals[] = array('id' => DIR_FS_WEB_ROOT, 'text' => DIR_FS_WEB_ROOT . $smname . $seoFunc->getFileState(OSF_DOC_ROOT.DIR_FS_WEB_ROOT.$smname));
  if(!in_array(array('id' => $sm_filepath, 'text' => $sm_filepath . $sm_filename . $seoFunc->getFileState(OSF_DOC_ROOT.$sm_filepath.$sm_filename)), $smbase_vals)) $smbase_vals[] = array('id' => $sm_filepath, 'text' => $sm_filepath . $sm_filename . $seoFunc->getFileState(OSF_DOC_ROOT.$sm_filepath.$sm_filename));
}



/// warn user if sitemap files that were just written do not exist
$sm_ok = false;
if(isset($_GET['result'])){
  if(is_file(OSF_DOC_ROOT . $sm_filepath . $sm_filename)){
    if(!is_writable(OSF_DOC_ROOT . $sm_filepath . $sm_filename)){
      $messageHandler->add(sprintf(OSF_SITEMAP_ERROR_RESTRICTED, $sm_url), FaqMessage::$error);
    }else{
      $sm_ok = true;
    }
  }else{
    $messageHandler->add(sprintf(OSF_SITEMAP_ERROR_NO_SM, $sm_url), FaqMessage::$error);
  }
}

require_once(DIR_FAQ_INCLUDES . 'js/faq_map_ui.js.php');
?>

<link href="../faq/styles/faq_sitemap.css" rel="stylesheet" type="text/css" media="all" />
<?php
// output system messages first
if ($messageHandler->size() > 0) echo $messageHandler->output() . '<hr />';
?>
<h1 class="handover" onclick="javascript:show_hide_help('explaingm', 'gmhelpicon');">
  <img class="handover" id="gmhelpicon" src="../faq/img/seo/help_white.png" alt="<?php echo OSF_MAPUI_TITLE; ?>" title="<?php echo OSF_MAPUI_TITLE; ?>" onclick="javascript:show_hide_help('explaingm', 'gmhelpicon');" />
  <?php echo OSF_PAGE_FAQ_SITEMAP; ?>
</h1>
<table id="explaingm" style="display:none;" width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td class="help_text"><?php echo OSF_SITEMAP_HELP; ?></td>
  </tr>
</table>



<form name="gmapform" action="<?php echo FaqFuncs::format_url(FILE_FAQ_ADMIN, 'mapbuilder=true', 'SSL'); ?>" method="post" style="margin:0; padding:0;">
	<div id="ticketthread">

      <h1 id="osf_working" style="display:none;color:#FE7700;"><?php echo OSF_WORKING; ?></h1>

			<table width="100%" border="0" cellspacing="0" cellpadding="2" class="message">
				<tr>
					<th><?php echo OSF_SITEMAP_OUTPUT; ?></th>
					<th><?php echo $faqForm->pulldown_menu('smap', $smap_vals, $sm_result, 'onchange="on_output_change(\'smap\',\'parent\');"', true); ?></th>
				</tr>
			</table>
<?php
echo $faqForm->hidden_field('output_type', $output_type);
echo $faqForm->hidden_field('output_size', $map_entry_count);

if($sm_result=='local'){
?>

      <p><b><?php echo OSF_DESCRIPTION; ?></b> <?php echo OSF_NEW_DESCRIPTION; ?></p>
      <h3 class="sm_alt_text"><?php echo OSF_NEW_TITLE; ?></h3>
      <table id="new_opts" width="100%" border="0" cellspacing="0" cellpadding="2" class="note">
        <tr>
					<th><?php echo OSF_SITEMAP_DEST; ?> <?php echo $faqForm->pulldown_menu('smbase', $smbase_vals, $sm_filepath, '', true); ?></th>
				</tr>
			</table>

<?php
}elseif($sm_result=='other'){
?>
			<p><b><?php echo OSF_DESCRIPTION; ?></b> <?php echo OSF_APD_DESCRIPTION; ?></p>
			<h3 class="sm_alt_text"><?php echo OSF_APD_TITLE; ?></h3>
			<table id="append_opts" width="100%" border="0" cellspacing="0" cellpadding="2" class="note">
<?php if(count($smappend_set['sitemap']) > 0){ ?>
		    <tr>
	        <th>
            <?php echo $faqForm->radio_field('append_to', 'sitemap', true); ?> <?php echo OSF_SM_OTHER_OPT1; ?><br />
            <?php echo OSF_APPEND_TO; ?> <?php echo $faqForm->pulldown_menu('sitemap_name', $smappend_set['sitemap'], OSF_DOC_ROOT . $sm_filepath . $sm_map); ?>
          </th>
		    </tr>
<?php }else{ ?>
        <tr>
          <th><?php echo OSF_APD_NO_SITEMAP; ?></th>
        </tr>
<?php } ?>
		    <tr>
          <td><hr /></td>
        </tr>
<?php if(count($smappend_set['sitemap_index']) > 0){ ?>
		    <tr>
	        <th>
            <?php echo $faqForm->radio_field('append_to', 'sitemap_index', false); ?> <?php echo OSF_SM_OTHER_OPT2; ?><br />
            <?php echo OSF_APPEND_TO; ?> <?php echo $faqForm->pulldown_menu('sitemap_index_name', $smappend_set['sitemap_index'], OSF_DOC_ROOT . $sm_filepath . $sm_index); ?>
	        </th>
		    </tr>
<?php }else{ ?>
        <tr>
          <th><?php echo OSF_APD_NO_INDEX; ?></th>
        </tr>
<?php } ?>
			</table>
<?php
}else{
?>
			<p id="test_opts"><b><?php echo OSF_DESCRIPTION; ?> </b>
			<?php echo OSF_TEST_DESCRIPTION; ?></p>
<?php
}
?>
      <hr /><p id="notify_container" style="display:none;"><?php echo $faqForm->checkbox_field('notify', $sm_notify, ($sm_notify=='ping' ? true : false), '') . OSF_SITEMAP_PING; ?></p>
      <br /><input class="handover" name="submit" type="submit" value="<?php echo OSF_SITEMAP_CREATE; ?>" />
			<br />
			<table id="result_opts" style="display:none;" width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
			    <td>
<?php
if($sm_ok){
  echo '<br /><hr /><h3 class="sm_alt_text">' . OSF_SM_DESCRIPTION . '</h3>';

  echo '<p class="smpad">';
  echo OSF_SM_LOCATION . ' ' . $faqForm->input_field('just_made', $sm_url, ' size="95" readonly="readonly" onfocus="selectText(\'just_made\', \'result_opts\');"');
  echo '</p>';

  echo '<p class="smpad">';
  echo OSF_SITEMAP_URL . '<b> <a href="' . $sm_url . '" target="_blank">' . FaqFuncs::format_image(IMG_ICON_PREVIEW, 'View') . ' ' . $sm_url . '</a>' . '</b>';
  echo '</p>';

  echo '<p class="smpad">';
  echo OSF_SM_ENTRIES . '<b> ' . number_format($map_entry_count, 0, '', ',')  . '</b> ';
  echo '</p>';

  echo '<p class="smpad">';
  echo OSF_SM_OUTPUT . '<b> ' . $seoFunc->get_real_name($output_type) . '</b> ';
  echo '</p>';

  echo '<p class="smpad">';
  echo OSF_SITEMAP_WRITE . '<b> ' . date(OSF_DATE_FMT_MED, filemtime(OSF_DOC_ROOT . $sm_filepath . $sm_filename)) . '</b> ';
  echo '</p>';

  echo '<script language="javascript1.2" type="text/javascript">selectText(\'just_made\', \'result_opts\');</script>';
}else{
  echo '&nbsp;';//stops odd formatting when no data is displayed
}
?>
        </td>
	    </tr>
		</table>
	</div>
</form>
<script language="javascript1.2" type="text/javascript">show_hide_div('notify_container', <?php echo ($sm_result=='test') ? 'false':'true'; ?>);</script>