<?php
/* *************************************************************************
  Id: faq_admin_ui.inc.php

  The main FAQ admin display page.
  Variables and functionality for this file
  are mainly handled by faq_admin_worker.inc.php


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

/// LANGUAGE FILE
require_once (DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/faq_admin_ui.lang.php');
/// FUNCTIONALITY
require_once (DIR_FAQ_INCLUDES . 'FaqPaginator.php');
require_once (DIR_FAQ_INCLUDES . 'FaqUpload.php');
require_once (DIR_FAQ_INCLUDES . 'FaqCrumb.php');
require_once (DIR_FAQ_INCLUDES . 'FaqAdmin.php');

$FaqCrumb = new FaqCrumb;
$faqAdmin = new FaqAdmin;
$pages = new FaqPaginator(FILE_FAQ_ADMIN);
require_once (DIR_FAQ_INCLUDES . 'staff/' . FILE_FAQ_ADMIN_WORKER);

/// OUTPUT
require_once (DIR_FAQ_INCLUDES . 'js/faq_verify.js.php');

// create file upload handlers
//TODO: add admin options for file types and size limits
switch ($action) {
  case 'new_faq':
  case 'new_faq_preview':
    $docup = new FileUpload("pdf", 1, array("pdf", "txt", "odt", "doc", "docx", "tab", "csv", "ods", "xls", "xlsx"), 5242880, DIR_FS_DOC, DIR_WS_DOC, OSF_DOCUMENT);

    $docup->permissions = 0777; //allows deletion. Must be an octal integer with leading 0 and not a string.
    $docup->showUploadBtn = false;

    $extensions = array('jpg','png','jpeg','gif');
    break;
  default:
    break;
}


// output system messages first
if ($messageHandler->size() > 0) echo $messageHandler->output() . '<hr />';




// display a FaqCrumb menu for faq admin
$faqAdmin->show_bc_menu();
echo '<hr />';





///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
/// new faq category / edit faq category
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
if ($action == 'new_category' || $action == 'edit_category') {
  if (($_GET['cID']) && (!$_POST)) {
   $categories_query = db_query("select id, parent_id, category, category_status, date_added, last_modified from " . TABLE_FAQCATS . " where id = '" . $_GET['cID'] . "' order by category");
   $category_data = db_fetch_array($categories_query);
   $cInfo = new FaqArrayData($category_data);
  } elseif ($_POST) {
   $cInfo = new FaqArrayData($_POST);
   $cInfo->parent_id=$current_faq_cat_id;
  } else {
   $cInfo = new FaqArrayData(array());
   $cInfo->parent_id=$current_faq_cat_id;
  }
  $text_new_or_edit = ($action == 'new_category') ? OSF_HEAD_INFO_NEW_CATEGORY : OSF_HEAD_INFO_EDIT_CATEGORY;
?>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
  <tr>
    <td><h1><?php echo sprintf($text_new_or_edit, $faqAdmin->get_output_cat_path($current_faq_cat_id)); ?></h1></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>

  <?php $form_action = ($_GET['cID']) ? 'update_category' : 'insert_category';
  echo $faqForm->form_open('new_category', FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'action', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&action=' . $form_action, 'post', 'onsubmit="return verify_cat();"'); ?>
  <tr>
    <td><b><?php echo OSF_CAT_NAME; ?></b><br />
    <?php echo $faqForm->input_field('category', $cInfo->category, 'style="width:295px;"'); ?></td>
  </tr>
  <tr>
    <?php  $fc_status = ((int)$cInfo->category_status == 1) ? true : false; ?>
    <td><b><?php echo OSF_HEAD_STATUS; ?>:</b><br />
    <?php  echo '&nbsp;' . $faqForm->radio_field('category_status', '1', ($fc_status)) . '&nbsp;' . OSF_FAQ_AVAILABLE . '&nbsp;' . $faqForm->radio_field('category_status', '0', !$fc_status) . '&nbsp;' . OSF_NOT_AVAILABLE; ?></td>
  </tr>
  <tr>
    <td align="right"><?php echo $faqForm->hidden_field('categories_date_added', (($cInfo->date_added) ? $cInfo->date_added : date('Y-m-d'))) . $faqForm->hidden_field('parent_id', $cInfo->parent_id); ?>
    <?php
    if ($_GET['cID']) {
      echo $faqForm->submit_image('button_update.gif', OSF_TIP_UPDATE);
    } else {
      echo $faqForm->submit_image('button_insert.gif', OSF_TIP_INSERT);
    }
    echo '&nbsp;&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'action', 'i', 'ipp')) . 'fcPath=' . $fcPath) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>'; ?>
    </td>
  </tr>
  </form>

  </table>
<?php










///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
/// new faq / edit faq
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
} elseif ($action == 'new_faq') {
  $editHeading = OSF_HEAD_NEW_FAQ;

  if (isset($_GET['fID'])) {
    $faq_query = db_query("select id, question, answer, faq_active, date_added, last_modified, name, email, phone, pdfupload from " . TABLE_FAQS . " where id = '" . (int)$_GET['fID'] . "'");
    $faq_data = db_fetch_array($faq_query);
    $fInfo = new FaqArrayData($faq_data);
    $editHeading = OSF_EDIT_FAQ;

    if(isset($_POST['question']) || isset($_POST['answer'])){
      $fInfo = new FaqArrayData($_POST);
    }

  } elseif (FaqFuncs::not_null($_POST)) {
    $fInfo = new FaqArrayData($_POST);
  } else {
    $fInfo = new FaqArrayData(array());
  }

  if (!isset($fInfo->faq_active)) $fInfo->faq_active = '1';
  switch ($fInfo->faq_active) {
    case '0':
      $in_status = false;
      $out_status = true;
      break;
    case '1':
    default:
      $in_status = true;
      $out_status = false;
  }
?>


<?php echo $faqForm->form_open('new_faq', FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&action=new_faq_preview', 'post', 'enctype="multipart/form-data"'); ?>
<table border="0" cellspacing="0" cellpadding="2" width="100%">
  <tr>
    <td colspan="2"><h1><?php echo sprintf($editHeading, $faqAdmin->get_output_cat_path($current_faq_cat_id)); ?></h1></td>
  </tr>
  <tr>
    <td colspan="2"><h2><?php echo OSF_HEAD_FAQ_DETAIL; ?></h2></td>
  </tr>
  <tr>
    <?php $f_status = ((int)$fInfo->faq_active == 1) ? true : false; ?>
    <td><?php echo OSF_STATUS; ?></td>
    <td><?php echo $faqForm->radio_field('faq_active', '1', ($f_status)) . '&nbsp;' . OSF_FAQ_AVAILABLE . '&nbsp;' . $faqForm->radio_field('faq_active', '0', !$f_status) . '&nbsp;' . OSF_NOT_AVAILABLE; ?></td>
  </tr>
  <tr>
    <td colspan="2"><hr /></td>
  </tr>
  <tr>
    <td valign="top"><?php echo OSF_QUESTION; ?></td>
    <td><?php echo $faqForm->input_field('question', $fInfo->question, 'style="width:595px;"'); ?></td>
  </tr>
  <tr>
    <td valign="top"><?php  echo OSF_FAQ_ANSWER; ?></td>
    <td><?php echo $faqForm->textarea_field('answer', 'soft', '70', '15', (isset($fInfo->answer) ? $fInfo->answer : ''), '', false); ?>
<?php
if(OSFDB_WYSIWYG_STAFF=='true' && is_dir(OSF_DOC_ROOT . DIR_FS_WEB_ROOT . 'faq/ckeditor/')){
?>
      <script type="text/javascript" src="../faq/ckeditor/<?php echo FAQ_CK_VERSION; ?>/ckeditor.js"></script>
      <script type="text/javascript" language="javascript">
        /* <![CDATA[ */

        // This call can be placed at any point after the
        // <textarea>, or inside a <head><script> in a
        // window.onload event handler.

        // Replace the <textarea id="editor"> with a CKEditor
        // instance, using default configurations.

        editor = CKEDITOR.replace( 'answer' );
        CKEDITOR.config.baseHref = '<?php echo HTTP_SERVER . DIR_FS_WEB_ROOT; ?>';
        CKEDITOR.config.contentsCss = '<?php echo DIR_FS_WEB_ROOT . 'faq/styles/faq.css'; ?>';
        CKEDITOR.config.bodyId = 'faqs';
        /* ]]> */
      </script>
<?php
}
?>
    </td>
  </tr>
  <tr>
    <td colspan="2"><hr /></td>
  </tr>

  <tr>
    <td><?php echo OSF_DOCUMENT_UPLOAD; ?></td>
    <td><?php echo $docup->processFiles(true, false, false, false) . $docup->drawForm() . '<br />' . (!empty($fInfo->pdfupload) ? ' <a href="' . DIR_WS_DOC . $fInfo->pdfupload . '" target="_blank">' . $fInfo->pdfupload . '</a> ' . $faqForm->checkbox_field('remove_pdf', '0', ((isset($_POST['remove_pdf']) && $_POST['remove_pdf'] == '1') ? true : false)) . OSF_REMOVE_DOC . '<br /><br />' : '') . $faqForm->hidden_field('pdfupload', (!empty($docup->file_names[0]) ? $docup->file_names[0] : $fInfo->pdfupload)); ?></td>
  </tr>
  <tr>
    <td colspan="2"><hr /></td>
  </tr>

  <tr>
    <td><?php
    echo OSF_IMAGE_UPLOADS . '<br />' . OSF_VALID_TYPES;
    $js_ext = '';
    foreach($extensions as $ext){
      echo $ext . ' ';
      $js_ext .= $ext . '|';
    }
    $js_ext = substr($js_ext, 0, -1);
    ?></td>
    <td>

<script type="text/javascript" src="<?php echo DIR_FAQ_INCLUDES; ?>js/jquery-1.4.2.min.js" ></script>
<script type="text/javascript" src="<?php echo DIR_FAQ_INCLUDES; ?>js/ajax-file-upload/ajaxupload.3.5.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo DIR_FAQ_INCLUDES; ?>js/ajax-file-upload/styles.css" />
<script type="text/javascript" >
  $(function(){
    var btnUpload=$('#upload');
    var status=$('#status');
    new AjaxUpload(btnUpload, {
      action: './faq_assist.php',
      name: 'img',
      onSubmit: function(file, ext){
        if (! (ext && /^(<?php echo $js_ext; ?>)$/.test(ext))){ // extension is not allowed
          status.text('<?php echo OSF_TYPES_ALLOWED; ?>');
          return false;
        }
        status.text('<?php echo OSF_UPLOADING; ?>');
      },
      onComplete: function(file, response){
        //On completion clear the status
        status.text('');
        //Add uploaded file to list
        if(response.substring(0, 7)==="success"){
          $('<li></li>').appendTo('#files').html('<img src="<?php echo DIR_WS_IMAGES; ?>'+file+'" alt="" /><br />'+file).addClass('success');
        } else{
          if(response=="error_extension"){
        	  $('<li></li>').appendTo('#files').html('<?php echo OSF_FAILED; ?>: '+file+'<br /><?php echo OSF_TYPES_ALLOWED; ?>').addClass('error');
          }else{
        	  $('<li></li>').appendTo('#files').html('<?php echo OSF_FAILED; ?>: '+file).addClass('error');
          }
        }
      }
    });
  });
</script>
<div id="upload" ><span><?php echo OSF_TEXT_IMAGES; ?><span></div><span id="status" ></span>
<ul id="files" ></ul>
<iframe style="width:100%; border:none; outline:none;" src="./faq_assist.php?img_browse=true"></iframe>
    </td>
  </tr>
  <tr>
    <td colspan="2"><hr /></td>
  </tr>

  <tr>
    <td colspan="2"><h2><?php echo OSF_HEAD_AUTHOR_DETAIL; ?></h2></td>
  </tr>
  <tr>
    <td><?php echo OSF_FAQ_AUTHOR; ?></td>
    <td><?php echo $faqForm->input_field('name', $fInfo->name, 'style="width:295px;"') . OSF_TEXT_PUBLIC; ?></td>
  </tr>
  <tr>
    <td><?php echo OSF_FAQ_EMAIL; ?></td>
    <td><?php echo $faqForm->input_field('email', $fInfo->email, 'style="width:295px;"') . OSF_TEXT_PRIVATE; ?></td>
  </tr>
  <tr>
    <td><?php echo OSF_FAQ_PHONE; ?></td>
    <td><?php echo $faqForm->input_field('phone', $fInfo->phone, 'style="width:295px;"') . OSF_TEXT_PRIVATE; ?></td>
  </tr>
  <tr>
    <td colspan="2"><hr /></td>
  </tr>
  <tr>
    <td colspan="2" align="right"><?php echo $faqForm->hidden_field('date_added', $fInfo->date_added) . $faqForm->submit_image('button_preview.gif', OSF_TIP_PREVIEW) . '&nbsp;&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'i', 'ipp')) . 'fcPath=' . $fcPath) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>'; ?></td>
  </tr>
</table>
</form>
<?php









///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
/// faq preview
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
} elseif ($action == 'new_faq_preview') {

  $needs_reposting = false;// validation flag

  if(isset($_POST['question'])){
    $fInfo = new FaqArrayData($_POST);
    if (!FaqFuncs::not_null($fInfo->date_added)) $fInfo->date_added = date(OSF_DATE_FMT_STD, mktime());

    // form validation (added: 2010-02-11)
    if(!FaqFuncs::not_null($fInfo->question) || !FaqFuncs::not_null($fInfo->answer)){
      $needs_reposting = true;

      if(strlen($fInfo->question) < 1){
        $fInfo->question = '<span style="color:red;font-weight:bold">' . OSF_WARN_QUESTION_EMPTY . '</span>';
      }
      if(strlen($fInfo->answer) < 1){
        $fInfo->answer = '<span style="color:red;font-weight:bold">' . OSF_WARN_ANSWER_EMPTY . '</span>';
      }
    }

  }else{
    $faq_query = db_query("select id, question, answer, name, email, phone, date_added, pdfupload from " . TABLE_FAQS . " where id = '" . (int)$_GET['fID'] . "'");
    $faq = db_fetch_array($faq_query);

    $fInfo = new FaqArrayData($faq);
  }

  /* Fix image paths displayed in the preview text with some DOM magic.
   * This is only for the preview display and doesn't alter the text to be saved.
   * Local inline image paths always get saved with a path starting from "faq/images",
   * thus to preview it from the admin area the images would not display.
   */
  libxml_use_internal_errors(true);//muffle DOM errors about html parsing
  $document = new DomDocument('1.0', 'utf-8');
  $document->formatOutput = false;
  $document->loadHTML($fInfo->answer);

  //fix inline images
  $params = $document->getElementsByTagName('img');

  foreach ($params as $param) {
  	$attVal = $param->getAttribute('src');
  	//preserve offsite images
  	if(substr($attVal, 0, 4) != 'http'){
    	$newVal = substr($attVal, strrpos($attVal, '/')+1);
    	$param->setAttribute('src', DIR_WS_IMAGES . $newVal);
  	}
  }
  $fInfo->answer = $document->saveHTML();

  //extract the body contents since dom will have built a complete doc
  preg_match('/<body>(.*)<\/body>/iUms', $fInfo->answer, $matches);
  $fInfo->answer = $matches[1];

  //cleanup
  unset($document);

  $form_action = (isset($_GET['fID'])) ? 'update_faq' : 'insert_faq';
  echo $faqForm->form_open($form_action, FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'action', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"');
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td><h2><?php echo OSF_Q; ?></h2>
    <h3><?php echo $fInfo->question; ?></h3></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><h2><?php echo OSF_A; ?></h2>
    <?php echo $fInfo->answer; ?></td>
  </tr>


<?php
  //authors details are only displayed when they exist
  if(FaqFuncs::not_null($fInfo->name) || FaqFuncs::not_null($fInfo->email) || FaqFuncs::not_null($fInfo->phone)){
?>
  <tr>
    <td><hr /></td>
  </tr>
<?php
  }
  if(FaqFuncs::not_null($fInfo->name)){
?>
  <tr>
    <td><?php echo '<b>' . OSF_FAQ_AUTHOR . '</b> ' . $fInfo->name; ?></td>
  </tr>
<?php
  }
  if(FaqFuncs::not_null($fInfo->email)){
?>
  <tr>
    <td><?php echo '<b>' . OSF_FAQ_EMAIL . '</b> ' . $fInfo->email; ?></td>
  </tr>
<?php
  }
  if(FaqFuncs::not_null($fInfo->phone)){
?>
  <tr>
    <td><?php echo '<b>' . OSF_FAQ_PHONE . '</b> ' . $fInfo->phone; ?></td>
  </tr>
<?php
  }
?>


  <tr>
    <td>
    <hr />
<?php
  $docup->processFiles(false, false, false, false);

  if (FaqFuncs::not_null($docup->file_names[0])) {
   $fInfo->pdfupload = $docup->file_names[0];
  }

  if (FaqFuncs::not_null($fInfo->pdfupload)) {
    if (!FaqFuncs::not_null($docup->file_names[0])) {
      echo OSF_DOCUMENT . ' <a href="' . DIR_WS_DOC . $fInfo->pdfupload . '" target="_blank">' .$fInfo->pdfupload. '</a> (' .FaqFuncs::display_filesize(filesize(DIR_FS_DOC . $fInfo->pdfupload)) . ')';
      echo (isset($_POST['remove_pdf']) ? ' <i>('.OSF_DOC_FOR_REMOVAL.')</i>' : '');
    }else{
      echo (isset($_POST['remove_pdf']) ? ' <i>('.OSF_DOC_FOR_REMOVAL.')</i>' : ' <i>('.OSF_DOC_FOR_UPLOAD.')</i>');
    }
  }
?>
    </td>
  </tr>
  <tr>
    <td align="center" class="smallText"><?php echo OSF_DATE_ADDED . '<b>' . FaqFuncs::format_date($fInfo->date_added) . '</b>'; ?></td>
  </tr>
  <tr>
    <td><hr /></td>
  </tr>
<?php
  if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
    if (isset($_GET['origin'])) {
      $pos_params = strpos($_GET['origin'], '?', 0);
      if ($pos_params != false) {
        $back_url = substr($_GET['origin'], 0, $pos_params);
        $back_url_params = substr($_GET['origin'], $pos_params + 1);
      } else {
        $back_url = $_GET['origin'];
        $back_url_params = FaqFuncs::get_all_get_params(array('fcPath', 'action', 'read', 'origin', 'i', 'ipp'));
      }
    } else {
      $back_url = FILE_FAQ_ADMIN;
      $back_url_params = FaqFuncs::get_all_get_params(array('fcPath', 'action', 'read', 'origin', 'i', 'ipp')) . 'fcPath=' . $fcPath;
    }
?>
  <tr>
    <td align="right"><?php echo '<a href="' . FaqFuncs::format_url($back_url, $back_url_params) . '">' . $faqForm->button_image('button_back.gif', OSF_TIP_BACK) . '</a>'; ?></td>
  </tr>
<?php
  } else {
?>
  <tr>
    <td align="right" class="smallText">
<?php
    /* Re-Post all POST variables */
    reset($_POST);
    while (list($key, $value) = each($_POST)) {
      if (!is_array($_POST[$key])) {
        echo $faqForm->hidden_field($key, htmlspecialchars(stripslashes($value)));
      }
    }
    echo $faqForm->hidden_field('pdfupload', stripslashes($fInfo->pdfupload));


    // fixed back button. returns to the edit area.
    echo $faqForm->submit_image('button_back.gif', OSF_TIP_BACK, 'onclick="document.'.$form_action.'.action=\''.FILE_FAQ_ADMIN . '?' . FaqFuncs::get_all_get_params(array('fcPath', 'action', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&action=new_faq'.'\';"') . '&nbsp;&nbsp;';

    if(!$needs_reposting){
      if (isset($_GET['fID'])) {
        echo $faqForm->submit_image('button_update.gif', OSF_TIP_UPDATE);
      } else {
        echo $faqForm->submit_image('button_insert.gif', OSF_TIP_INSERT);
      }
    }

    echo '&nbsp;&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'flag', 'read', 'i', 'ipp')) . 'fcPath=' . $fcPath) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>';
?></td>
  </tr>
<?php
  }
?>
</table>
</form>
<?php










///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
/// DEFAULT faq categories and faqs list
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////

/* TODO: apply pagination to cats and faqs as a single list. At the moment only faqs get paginated.
 * Will try to have this done for the next release.
 */

} else {


//////////////////////////////////
/// Filtering and search forms ///
//////////////////////////////////


//sort by column header functionality
if($_GET['direction']=='ASC'){
	$sort_direction = 'ASC';
	$alt_direction = 'DESC';
	$direction_char = '<span title="' . OSF_HEAD_SORT_DESC . '"> -<span>';
}else{
	$sort_direction = 'DESC';
	$alt_direction = 'ASC';
	$direction_char = '<span title="' . OSF_HEAD_SORT_ASC . '"> +<span>';
}

$shd_name = '';
$shd_status = '';
$shd_feature = '';
$shd_views = '';

$osf_cat_order = 'order by ';
$osf_faq_order = 'order by ';
$sort_by = (isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id');
switch($sort_by){
	case 'views':
		$osf_cat_order .= 'client_views ' . $sort_direction . ', category DESC';
		$osf_faq_order .= 'f.client_views ' . $sort_direction . ', f.question DESC';
		$shd_views = $direction_char;
		break;
	case 'status':
		$osf_cat_order .= 'category_status ' . $sort_direction . ', category DESC';
		$osf_faq_order .= 'f.faq_active ' . $sort_direction . ', f.question DESC';
		$shd_status = $direction_char;
		break;
	case 'feature':
		$osf_cat_order .= 'show_on_nonfaq ' . $sort_direction . ', category DESC';
		$osf_faq_order .= 'f.show_on_nonfaq ' . $sort_direction . ', f.question DESC';
		$shd_feature = $direction_char;
		break;
	case 'name':
	default:
		$osf_cat_order .= 'category ' . $sort_direction;
		$osf_faq_order .= 'f.question ' . $sort_direction;
		$shd_name = $direction_char;
		break;
}
//sort by column header functionality
$sort_params = FaqFuncs::get_all_get_params(array('sort_by', 'direction', 'action', 'page', 'pg', 'i', 'ipp'));
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td><h1><?php echo OSF_MAIN_TITLE . ' - <i>' . OSF_PAGE_FAQ . '</i>'; ?></h1>
<?php


  //print('<pre>');print_r($_GET);print('</pre>');

  $free_browse = (isset($_GET['free_browse']) && $_GET['free_browse']==OSF_BTN_YES) ? true : false;


  echo $faqForm->form_open('goto', FILE_FAQ_ADMIN, '', 'get', 'style="margin:0;padding;0;border:0;display:inline"');
  echo OSF_HEAD_TITLE_GOTO . ' ' . $faqForm->pulldown_menu('fcPath', $faqAdmin->get_tree(), $current_faq_cat_id, 'onchange="this.form.submit();"' . ($free_browse ? ' disabled="disabled"':''));
  echo FaqFuncs::get_all_get_params_hidden(array('action', 'fcPath', 'free_browse', 'page', 'pg', 'i', 'ipp'));
  echo '</form>';


  echo $faqForm->form_open('noGoto', FILE_FAQ_ADMIN, '', 'get', 'style="margin:0;padding;0;border:0;display:inline"');
  echo OSF_SHOW_ALL;
  echo '<input type="submit" value="'.OSF_BTN_YES.'" name="free_browse"' . ($free_browse ? ' disabled="disabled"':'') . ' /> <input type="submit" value="'.OSF_BTN_NO.'" name="free_browse"' . (!$free_browse ? ' disabled="disabled"':'') . ' />';
  echo FaqFuncs::get_all_get_params_hidden(array('action', 'fcPath', 'free_browse', 'page', 'pg', 'i', 'ipp'));
  echo '</form>';


  //spacer between filter subjects
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';


  $sfilter = isset($_GET['sfilter']) ? (int)$_GET['sfilter'] : -1;
  $statusSet = array(array('id' => -1, 'text' => OSF_ANY_STATUS),
                     array('id' => 0, 'text' => OSF_DISABLED),
                     array('id' => 1, 'text' => OSF_ENABLED));

  echo $faqForm->form_open('status_filter', FILE_FAQ_ADMIN, '', 'get', 'style="margin:0;padding;0;border:0;display:inline"');
  echo '&nbsp;&nbsp;' . $faqForm->pulldown_menu('sfilter', $statusSet, $sfilter, 'onchange="this.form.submit();"');
  echo FaqFuncs::get_all_get_params_hidden(array('action', 'sfilter', 'page', 'pg', 'i', 'ipp'));
  echo '</form>';

?>
    </td>
    <td align="right">&nbsp;</td>
    <td align="right" class="smallText">
<?php
  echo $faqForm->form_open('search', FILE_FAQ_ADMIN, '', 'get');
  echo OSF_HEAD_TITLE_SEARCH . ' ' . $faqForm->input_field('search');
  echo FaqFuncs::get_all_get_params_hidden(array('action', 'fcPath', 'search', 'page'));
  if (isset($_GET['search']) && FaqFuncs::not_null($_GET['search'])) {
    echo '<br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('search', 'action', 'page', 'pg', 'i', 'ipp'))) . '">'.OSF_CLEAR_SEARCH.'</a>';
  }
  echo '</form>';
?>
    </td>
  </tr>
</table>

<?php
////////////////////////////
/// The rest of the page ///
////////////////////////////
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td valign="top">
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dtable">
        <tr>
          <th><?php echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, $sort_params . 'sort_by=name&direction='.$alt_direction) . '">'.OSF_HEAD_CATS_FAQS.$shd_name.'</a>'; ?></th>
          <th align="center"><?php echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, $sort_params . 'sort_by=status&direction='.$alt_direction) . '">'.OSF_HEAD_STATUS.$shd_status.'</a>'; ?></th>
          <th align="center"><?php echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, $sort_params . 'sort_by=feature&direction='.$alt_direction) . '">'.OSF_HEAD_FEATURED.$shd_feature.'</a>'; ?></th>
          <th align="center"><?php echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, $sort_params . 'sort_by=views&direction='.$alt_direction) . '">'.OSF_HEAD_VIEWS.$shd_views.'</a>'; ?></th>
          <th align="right"><?php echo OSF_HEAD_ACTION; ?>&nbsp;</th>
        </tr>
<?php
  // summary counters
  $categories_count = 0;
  $faq_count = 0;
  $rows = 0;


  ////////////////////////////////////////////////////////////////////
  /// FAQ Categories
  ////////////////////////////////////////////////////////////////////
  $sfilter_sql = '';//status filter for faqcats
  if (isset($_GET['search']) && FaqFuncs::not_null($_GET['search'])) {

    /// Free browse filter
    if($free_browse && $sfilter > -1){
      $sfilter_sql = ' and category_status = ' . (($sfilter == 0) ? 0:1);
    }

    $search = db_input($_GET['search'], false);
    $categories_query = db_query("select id, category, category_status, show_on_nonfaq, parent_id, client_views, date_added, last_modified from " . TABLE_FAQCATS . " where category like '%" . db_input($search, false) . "%'" . $sfilter_sql . " " . $osf_cat_order);
  } else {
    unset($_GET['search']);

    /// Free browse filter
    if($free_browse){
      if($sfilter > -1){
        $sfilter_sql = ' where category_status = ' . (($sfilter == 0) ? 0:1);
      }else{
        $sfilter_sql = '';
      }
      $catFilter = '';
    }else{
      if($sfilter > -1){
        $sfilter_sql = ' and category_status = ' . (($sfilter == 0) ? 0:1);
      }else{
        $sfilter_sql = '';
      }
      $catFilter = " where parent_id = '" . (int)$current_faq_cat_id . "'";
    }

    $categories_query = db_query("select id, category, category_status, show_on_nonfaq, parent_id, client_views, date_added, last_modified from " . TABLE_FAQCATS . $catFilter . $sfilter_sql . " " . $osf_cat_order);
  }

  while ($categories = db_fetch_array($categories_query)) {
    $categories_count++;
    $rows++;
    // Get parent_id for subcategories if search
    if (isset($_GET['search']) && FaqFuncs::not_null($_GET['search'])) $fcPath = $categories['parent_id'];

    if ((!isset($_GET['cID']) && !isset($_GET['fID']) || (isset($_GET['cID']) && ($_GET['cID'] == $categories['id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $category_childs = array('childs_count' => $faqAdmin->count_subcats_in_cat($categories['id']));
      $category_faqs = array('faqs_count' => $faqAdmin->count_faqs_in_cat($categories['id']));

      $cInfo_array = array_merge($categories, $category_childs, $category_faqs);
      $cInfo = new FaqArrayData($cInfo_array);
    }
    if (isset($cInfo) && is_object($cInfo) && ($categories['id'] == $cInfo->id)) {
      echo '        <tr class="row2" onclick="document.location.href=\'' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'pg', 'page', 'i', 'ipp')) . $faqAdmin->get_faq_path($categories['id'])) . '\'">' . "\n";
    } else {
      echo '        <tr class="row1" onclick="document.location.href=\'' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'pg', 'page', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&cID=' . $categories['id']) . '\'">' . "\n";
    }
?>
          <td><?php echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'pg', 'page', 'i', 'ipp')) . $faqAdmin->get_faq_path($categories['id'])) . '">' . FaqFuncs::format_image(IMG_ICON_FOLDER, OSF_TIP_FOLDER) . '</a>&nbsp;<b>' . $categories['category'] . '</b>'; ?></td>

          <td align="center" class="nohover" style="background-color:<?php echo ($categories['category_status'] == '1') ? OSFDB_ACTIVE_COLOR : OSFDB_INACTIVE_COLOR; ?>;">
<?php
    if ($categories['category_status'] == '1') {
      echo FaqFuncs::format_image(IMG_ICON_GREEN_ACTIVE, OSF_TIP_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'flag', 'pg', 'page', 'i', 'ipp')) . 'action=setflag_categories&flag=0&fcPath=' . $fcPath . '&cID=' . $categories['id']) . '">' . FaqFuncs::format_image(IMG_ICON_RED_DOWN, OSF_TIP_STATUS_RED_LIGHT, 10, 10) . '</a>';
    } else {
      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'flag', 'pg', 'page', 'i', 'ipp')) . 'action=setflag_categories&flag=1&fcPath=' . $fcPath . '&cID=' . $categories['id']) . '">' . FaqFuncs::format_image(IMG_ICON_GREEN_DOWN, OSF_TIP_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . FaqFuncs::format_image(IMG_ICON_RED_ACTIVE, OSF_TIP_STATUS_RED, 10, 10);
    }
?></td>

          <td align="center" class="nohover" style="background-color:<?php echo ($categories['show_on_nonfaq'] == '1') ? OSFDB_ACTIVE_COLOR : OSFDB_INACTIVE_COLOR; ?>;">
<?php
    if ($categories['show_on_nonfaq'] == '1') {
      echo FaqFuncs::format_image(IMG_ICON_GREEN_ACTIVE, OSF_TIP_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'flag', 'pg', 'page', 'i', 'ipp')) . 'action=setfav_categories&flag=0&fcPath=' . $fcPath . '&cID=' . $categories['id']) . '">' . FaqFuncs::format_image(IMG_ICON_RED_DOWN, OSF_TIP_STATUS_RED_LIGHT, 10, 10) . '</a>';
    } else {
      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'flag', 'pg', 'page', 'i', 'ipp')) . 'action=setfav_categories&flag=1&fcPath=' . $fcPath . '&cID=' . $categories['id']) . '">' . FaqFuncs::format_image(IMG_ICON_GREEN_DOWN, OSF_TIP_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . FaqFuncs::format_image(IMG_ICON_RED_ACTIVE, OSF_TIP_STATUS_RED, 10, 10);
    }
?></td>

          <td align="right"><?php echo $categories['client_views']; ?></td>

          <td align="right">
<?php
    if (isset($cInfo) && is_object($cInfo) && ($categories['id'] == $cInfo->id)) {
      echo FaqFuncs::format_image(IMG_ICON_ARROW_RIGHT, '');
    } else {
      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'action', 'flag', 'pg', 'page', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&cID=' . $categories['id']) . '">' . FaqFuncs::format_image(IMG_ICON_INFO, OSF_TIP_INFO) . '</a>';
    }
?>&nbsp;</td>
        </tr>
<?php
  }



  ////////////////////////////////////////////////////////////////////
  /// FAQs
  ////////////////////////////////////////////////////////////////////
  $sfilter_sql = '';//status filter for faqs
  if($sfilter > -1){
    $sfilter_sql = ' and f.faq_active = ' . (($sfilter == 0) ? 0:1);
  }



  if (isset($_GET['search']) && FaqFuncs::not_null($_GET['search'])) {

    /// faq pagination count
    $faq_count_query = db_query("select COUNT(f.id) as faq_count from " . TABLE_FAQS . " f, " . TABLE_FAQS2FAQCATS . " f2f where f.id = f2f.faq_id and f.question like '%" . db_input($search, false) . "%'" . $sfilter_sql);
    $faq_row_count = db_fetch_array($faq_count_query);

    $pages->items_total = (int)$faq_row_count['faq_count'];
    $pages->paginate();

    $fInfo_query = db_query("select f.id, f.question, f.answer, f.faq_active, f.show_on_nonfaq, f.client_views, f.date_added, f.last_modified, f.name, f.email, f.phone, f2f.faqcategory_id from " . TABLE_FAQS . " f, " . TABLE_FAQS2FAQCATS . " f2f where f.id = f2f.faq_id and f.question like '%" . db_input($search, false) . "%'" . $sfilter_sql . " " . $osf_faq_order . (($pages->items_total > 0) ? " " . $pages->limit : ""));
  } else {
    unset($_GET['search']);

    /// Free browse filter
    $faqFilter = $free_browse ? '' : "f2f.faqcategory_id = '" . (int)$current_faq_cat_id . "' and ";

    /// faq pagination count
    $faq_count_query = db_query("select COUNT(f.id) as faq_count from " . TABLE_FAQS . " f, " . TABLE_FAQS2FAQCATS . " f2f where " . $faqFilter . "f.id = f2f.faq_id" . $sfilter_sql);
    $faq_row_count = db_fetch_array($faq_count_query);

    $pages->set_items_total((int)$faq_row_count['faq_count']);
    /* **************************************************
     * START pagination control for remote requests.
     ************************************************** */
    if(!isset($_GET['pg']) && !isset($_GET['page']) && isset($_GET['fID'])){
      $pgConfigQuery = $sqle->db_compile(TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f"
                                        ,array('f.id')
                                        ,FaqSQLExt::$SELECT
                                        ,$faqFilter . "f.id = f2f.faq_id" . $sfilter_sql
                                        ,''
                                        ,'f.question');

      $pgCount = 0;
      while ($pgConfig = db_fetch_array($pgConfigQuery)) {
        ++$pgCount;
        if($_GET['fID']==$pgConfig['id']) break;
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

    $fInfo_query = db_query("select f.id, f.question, f.answer, f.faq_active, f.show_on_nonfaq, f.client_views, f.date_added, f.last_modified, f.name, f.email, f.phone from " . TABLE_FAQS . " f, " . TABLE_FAQS2FAQCATS . " f2f where " . $faqFilter . "f.id = f2f.faq_id" . $sfilter_sql . " " . $osf_faq_order . (($pages->items_total > 0) ? " " . $pages->limit : ""));
  }
  while ($fInfo_array = db_fetch_array($fInfo_query)) {
    $faq_count++;
    $rows++;

    // Get categories_id for faq if search
    if (isset($_GET['search']) && FaqFuncs::not_null($_GET['search'])) $fcPath = $fInfo_array['faqcategory_id'];

    if ((( !isset($_GET['fID']) && !isset($fInfo) && !isset($cInfo) ) || (isset($_GET['fID']) && ($_GET['fID'] == $fInfo_array['id'])) ) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $_GET['fID'] = $fInfo_array['id'];
      $fInfo = new FaqArrayData($fInfo_array);
    }
    if (isset($fInfo) && is_object($fInfo) && ($fInfo_array['id'] == $fInfo->id)) {
      echo '        <tr class="row2" onclick="document.location.href=\'' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'read', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo_array['id'] . '&action=new_faq_preview&read=only') . '\'">' . "\n";
    } else {
      echo '        <tr class="row1" onclick="document.location.href=\'' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'read', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo_array['id']) . '\'">' . "\n";
    }
?>
          <td><?php echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'read')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo_array['id'] . '&action=new_faq_preview&read=only') . '">' . FaqFuncs::format_image(IMG_ICON_PREVIEW, OSF_TIP_PREVIEW) . '</a>&nbsp;' . $fInfo_array['question']; ?></td>

          <td align="center" class="nohover" style="background-color:<?php echo ($fInfo_array['faq_active'] == '1') ? OSFDB_ACTIVE_COLOR : OSFDB_INACTIVE_COLOR; ?>;">
<?php
    if ($fInfo_array['faq_active'] == '1') {
      echo FaqFuncs::format_image(IMG_ICON_GREEN_ACTIVE, OSF_TIP_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'action', 'flag', 'read', 'i', 'ipp')) . 'action=setflag&flag=0&fID=' . $fInfo_array['id'] . '&fcPath=' . $fcPath) . '">' . FaqFuncs::format_image(IMG_ICON_RED_DOWN, OSF_TIP_STATUS_RED_LIGHT, 10, 10) . '</a>';
    } else {
      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'read')) . 'action=setflag&flag=1&fID=' . $fInfo_array['id'] . '&fcPath=' . $fcPath) . '">' . FaqFuncs::format_image(IMG_ICON_GREEN_DOWN, OSF_TIP_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . FaqFuncs::format_image(IMG_ICON_RED_ACTIVE, OSF_TIP_STATUS_RED, 10, 10);
    }
?></td>

          <td align="center" class="nohover" style="background-color:<?php echo ($fInfo_array['show_on_nonfaq'] == '1') ? OSFDB_ACTIVE_COLOR : OSFDB_INACTIVE_COLOR; ?>;">
<?php
    if ($fInfo_array['show_on_nonfaq'] == '1') {
      echo FaqFuncs::format_image(IMG_ICON_GREEN_ACTIVE, OSF_TIP_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'action', 'flag', 'read', 'i', 'ipp')) . 'action=setfav&flag=0&fID=' . $fInfo_array['id'] . '&fcPath=' . $fcPath) . '">' . FaqFuncs::format_image(IMG_ICON_RED_DOWN, OSF_TIP_STATUS_RED_LIGHT, 10, 10) . '</a>';
    } else {
      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'read', 'i', 'ipp')) . 'action=setfav&flag=1&fID=' . $fInfo_array['id'] . '&fcPath=' . $fcPath) . '">' . FaqFuncs::format_image(IMG_ICON_GREEN_DOWN, OSF_TIP_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . FaqFuncs::format_image(IMG_ICON_RED_ACTIVE, OSF_TIP_STATUS_RED, 10, 10);
    }
?></td>

          <td align="right"><?php echo $fInfo_array['client_views']; ?></td>

          <td align="right">
<?php
    if (isset($fInfo) && is_object($fInfo) && ($fInfo_array['id'] == $fInfo->id)) {
      echo FaqFuncs::format_image(IMG_ICON_ARROW_RIGHT, '');
    } else {
      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'flag', 'read', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo_array['id']) . '">' . FaqFuncs::format_image(IMG_ICON_INFO, OSF_TIP_INFO) . '</a>';
    }
?>&nbsp;</td>
        </tr>
<?php
  }
?>
      </table>
<?php
  // pagination controls
  //if ($pages->items_total > $faq_count) {
    echo '<div class="paginate_row">';
    echo $pages->display_pages(); // page numbers
    echo $pages->display_jump_menu(); // page jump menu
    echo $pages->display_items_per_page(); // items per page menu
    echo '</div>';
  //}
?>
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td class="smallText"><?php  echo OSF_CATEGORIES . '&nbsp;' . $categories_count . (($faq_count > 0) ? '<br />' . OSF_FAQS . '&nbsp;' . $faq_count . OSF_OF . $pages->items_total : ''); ?></td>
          <td align="right" class="smallText">
<?php
  /// back button
  if (sizeof($fcPath_array) > 0) echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'cID', 'action', 'flag', 'read', 'pg', 'page', 'i', 'ipp')) . ( (sizeof($fcPath_array)>1) ? 'fcPath='.$fcPath_array[sizeof($fcPath_array)-2] : '' ), 'SSL') . '">' . $faqForm->button_image('button_back.gif', OSF_TIP_BACK) . '</a>&nbsp;';

  if($free_browse){
    echo OSF_FREE_BROWSE_MODE;
  }else{
    if (!isset($_GET['search'])){
      echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'cID', 'action', 'flag', 'read', 'pg', 'page', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&action=new_category') . '">' . $faqForm->button_image('button_new_category.gif', OSF_TIP_NEW_CAT) . '</a>';

      echo '&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'cID', 'action', 'flag', 'read', 'pg', 'page', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&action=new_faq') . '">' . $faqForm->button_image('button_new_faq.gif', OSF_TIP_NEW_FAQ) . '</a>';
//      if ($current_faq_cat_id > 0) echo '&nbsp;<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'fID', 'cID', 'action', 'flag', 'read', 'pg', 'page', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&action=new_faq') . '">' . $faqForm->button_image('button_new_faq.gif', OSF_TIP_NEW_FAQ) . '</a>';
//      else  echo '<div style="width:120px;">' . OSF_INFO_TOP . '</div>';

    }else{
      echo '<div style="width:120px;">' . OSF_INFO_SEARCH . '</div>';
    }

  }
?>&nbsp;</td>
        </tr>
      </table>
    </td>
<?php









///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
/// DEFAULT side column
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////

  // build the side column
  $heading = '';
  $contents = array();
  switch ($action) {
    case 'delete_category':
      $heading = '<b>' . OSF_HEAD_INFO_DELETE_CATEGORY . '</b>';

      $contents[] = array('form' => $faqForm->form_open('categories', FILE_FAQ_ADMIN, 'action=delete_category_confirm&fcPath=' . $fcPath) . $faqForm->hidden_field('cat_id', $cInfo->id));
      $contents[] = array('text' => OSF_DELETE_CAT_INTRO . FaqFuncs::get_all_get_params_hidden(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'i', 'ipp')));
      $contents[] = array('text' => '<br /><b>' . $cInfo->category . '</b>');
      if ($cInfo->childs_count > 0) $contents[] = array('text' => '<br />' . sprintf(OSF_DELETE_WARNING_CHILDS, $cInfo->childs_count));
      if ($cInfo->faqs_count > 0) $contents[] = array('text' => '<br />' . sprintf(OSF_DELETE_WARNING_FAQS, $cInfo->faqs_count));
      $contents[] = array('align' => 'center', 'text' => '<br />' . $faqForm->submit_image('button_delete.gif', OSF_TIP_DELETE) .
      	'<br /><br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&cID=' . $cInfo->id) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>');

      break;

    case 'move_category':
      $heading = '<b>' . OSF_HEAD_INFO_MOVE_CATEGORY . '</b>';

      $contents[] = array('form' => $faqForm->form_open('categories', FILE_FAQ_ADMIN, 'action=move_cat_confirm&fcPath=' . $fcPath) . $faqForm->hidden_field('cat_id', $cInfo->id));
      $contents[] = array('text' => sprintf(OSF_INTRO_MOVE_CATEGORIES, $cInfo->category) . FaqFuncs::get_all_get_params_hidden(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'i', 'ipp')));
      $contents[] = array('text' => '<br />' . sprintf(OSF_TEXT_MOVE, $cInfo->category) . '<br />' . $faqForm->pulldown_menu('move_to_cat_id', $faqAdmin->get_tree(0, '', $cInfo->id), $current_faq_cat_id));
      $contents[] = array('align' => 'center', 'text' => '<br />' . $faqForm->submit_image('button_move.gif', OSF_TIP_MOVE) .
      	'<br /><br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&cID=' . $cInfo->id) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>');

      break;

    case 'delete_faq':
      $heading = '<b>' . OSF_HEAD_INFO_DELETE_FAQ . '</b>';

      $contents[] = array('form' => $faqForm->form_open('faqs', FILE_FAQ_ADMIN, 'action=delete_faq_confirm&fcPath=' . $fcPath) . $faqForm->hidden_field('faq_id', $fInfo->id));
      $contents[] = array('text' => OSF_DELETE_FAQ_INTRO . FaqFuncs::get_all_get_params_hidden(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')));
      $contents[] = array('text' => '<br /><b>Q:</b><br />' . $fInfo->question);
      //$contents[] = array('text' => '<br /><br /><b>A:</b><br />' . $fInfo->answer);
      $faq_categories_string = '';
      $faq_categories = $faqAdmin->get_cat_path_array($fInfo->id, 'faqs');
      for ($i = 0, $n = sizeof($faq_categories); $i < $n; $i++) {
        $category_path = '';
        for ($j = 0, $k = sizeof($faq_categories[$i]); $j < $k; $j++) {
          $category_path .= $faq_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
        }
        $category_path = substr($category_path, 0, -16);
        $faq_categories_string .= $faqForm->checkbox_field('faq_categories[]', $faq_categories[$i][sizeof($faq_categories[$i]) - 1]['id'], true) . '&nbsp;' . $category_path . '<br />';
      }
      $faq_categories_string = substr($faq_categories_string, 0, -4);
      $contents[] = array('text' => '<br />' . $faq_categories_string);
      $contents[] = array('align' => 'center', 'text' => '<br />' . $faqForm->submit_image('button_delete.gif', OSF_TIP_DELETE) .
      	'<br /><br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo->id) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>');

      break;

    case 'move_faq':
      $heading = '<b>' . OSF_HEAD_INFO_MOVE_FAQ . '</b>';

      $contents[] = array('form' => $faqForm->form_open('faqs', FILE_FAQ_ADMIN, 'action=move_faq_confirm&fcPath=' . $fcPath) . $faqForm->hidden_field('faq_id', $fInfo->id));
      $contents[] = array('text' => sprintf(OSF_MOVE_FAQS_INTRO, $fInfo->question) . FaqFuncs::get_all_get_params_hidden(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')));
      $contents[] = array('text' => '<br />' . OSF_INFO_CURRENT_CATEGORIES . '<br /><b>' . $faqAdmin->get_output_cat_path($fInfo->id, 'faqs') . '</b>');
      $contents[] = array('text' => '<br />' . sprintf(OSF_TEXT_MOVE, $fInfo->question) . '<br />' . $faqForm->pulldown_menu('move_to_cat_id', $faqAdmin->get_tree(0, ''), $current_faq_cat_id));
      $contents[] = array('align' => 'center', 'text' => '<br />' . $faqForm->submit_image('button_move.gif', OSF_TIP_MOVE) .
      	'<br /><br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo->id) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>');

      break;

    case 'copy_to':
      $heading = '<b>' . OSF_HEAD_INFO_COPY_TO . '</b>';

      $contents[] = array('form' => $faqForm->form_open('copy_to', FILE_FAQ_ADMIN, 'action=copy_to_confirm&fcPath=' . $fcPath) . $faqForm->hidden_field('faq_id', $fInfo->id));
      $contents[] = array('text' => OSF_INFO_COPY_TO_INTRO . FaqFuncs::get_all_get_params_hidden(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')));
      $contents[] = array('text' => '<br />' . OSF_INFO_CURRENT_CATEGORIES . '<br /><b>' . $faqAdmin->get_output_cat_path($fInfo->id, 'faqs') . '</b>');
      $contents[] = array('text' => '<br />' . OSF_CATEGORIES . '<br />' . $faqForm->pulldown_menu('cat_id', $faqAdmin->get_tree(0, ''), $current_faq_cat_id));
      $contents[] = array('text' => '<br />' . OSF_HOW_TO_COPY . '<br />' . $faqForm->radio_field('copy_as', 'link', true) . ' ' . OSF_COPY_AS_LINK . '<br />' . $faqForm->radio_field('copy_as', 'duplicate') . ' ' . OSF_COPY_AS_DUPLICATE);
      $contents[] = array('align' => 'center', 'text' => '<br />' . $faqForm->submit_image('button_copy.gif', OSF_TIP_COPY) .
        '<br /><br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo->id) . '">' . $faqForm->button_image('button_cancel.gif', OSF_TIP_CANCEL) . '</a>');

      break;
    default:
      if ($rows > 0) {
        if (isset($cInfo) && is_object($cInfo)) { // category info
          $heading = '<b>' . $cInfo->category . '</b>';

          $contents[] = array('align' => 'center', 'text' =>
            '<br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&cID=' . $cInfo->id . '&action=edit_category') . '">' . $faqForm->button_image('button_edit.gif', OSF_TIP_EDIT) . '</a><br /><br />
            <a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&cID=' . $cInfo->id . '&action=delete_category') . '">' . $faqForm->button_image('button_delete.gif', OSF_TIP_DELETE) . '</a><br /><br />
            <a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&cID=' . $cInfo->id . '&action=move_category') . '">' . $faqForm->button_image('button_move.gif', OSF_TIP_MOVE) . '</a>');

          $contents[] = array('text' => '<hr /><b>' . $cInfo->category . '</b>');

          $contents[] = array('text' => '<hr />' . OSF_DATE_ADDED . ' ' . FaqFuncs::format_date($cInfo->date_added));
          if (FaqFuncs::not_null($cInfo->last_modified)) $contents[] = array('text' => OSF_LAST_MODIFIED . ' ' . FaqFuncs::format_date($cInfo->last_modified));

          $contents[] = array('text' => '<br />' . OSF_SUBCATEGORIES . ' ' . $cInfo->childs_count . '<br />' . OSF_FAQS . ' ' . $cInfo->faqs_count);


        } elseif (isset($fInfo) && is_object($fInfo)) { // faq info
          $heading = '<b>' . $fInfo->question . '</b>';

          $contents[] = array('align' => 'center', 'text' =>
            '<br /><a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo->id . '&action=new_faq') . '">' . $faqForm->button_image('button_edit.gif', OSF_TIP_EDIT) . '</a><br /><br />
            <a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo->id . '&action=delete_faq') . '">' . $faqForm->button_image('button_delete.gif', OSF_TIP_DELETE) . '</a><br /><br />
            <a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo->id . '&action=move_faq') . '">' . $faqForm->button_image('button_move.gif', OSF_TIP_MOVE) . '</a><br /><br />
            <a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, FaqFuncs::get_all_get_params(array('fcPath', 'cID', 'fID', 'action', 'cat_id', 'faq_id', 'i', 'ipp')) . 'fcPath=' . $fcPath . '&fID=' . $fInfo->id . '&action=copy_to') . '">' . $faqForm->button_image('button_copy_to.gif', OSF_TIP_COPY_TO) . '</a>');

          $contents[] = array('text' => '<hr /><b>' . OSF_Q . ':</b><br />' . $fInfo->question);
          $contents[] = array('text' => '<br /><b>' . OSF_A . ':</b><br />' . substr(strip_tags($fInfo->answer), 0, 255) . '...');
          $contents[] = array('text' => '<hr />');

          if(FaqFuncs::not_null($fInfo->name)){
            $contents[] = array('text' => '<br /><b>' . OSF_FAQ_AUTHOR . '</b> ' . $fInfo->name);
            $contents[] = array('text' => '<b>' . OSF_FAQ_EMAIL . '</b> ' . $fInfo->email);
            $contents[] = array('text' => '<b>' . OSF_FAQ_PHONE . '</b> ' . $fInfo->phone . '<br />');
          }

          $contents[] = array('text' => OSF_DATE_ADDED . ' ' . FaqFuncs::format_date($fInfo->date_added));
          if (FaqFuncs::not_null($fInfo->last_modified)) $contents[] = array('text' => OSF_LAST_MODIFIED . ' ' . FaqFuncs::format_date($fInfo->last_modified));

        }
      } else { // create category/faq info
        $heading = '<b>' . OSF_EMPTY_CATEGORY . '</b>';

        $contents[] = array('text' => OSF_NO_CHILDS);
      }
      break;
  }


  // display the side column
  $faqTable = new FaqTable;
  if (!FaqFuncs::not_null($heading) && !FaqFuncs::not_null($contents)) {
    $heading = '<b>'.OSF_HEAD_NO_SELECTION.'</b>';
    $contents[] = array('text' => OSF_SELECT_A_ROW);
  }

  echo '    <td width="25%" valign="top">' . "\n";
  echo $faqTable->detailTable($heading, $contents);
  echo '    </td>' . "\n";
?>
  </tr>
</table>
<?php
}
?>