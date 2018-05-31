<?php
/* *************************************************************************
 Id: faq_map_builder.inc.php

 xml Sitemap Builder.
 This file builds xml Sitemaps for Search Engine Indexing bots.
 XML-Specifications: http://www.sitemaps.org/protocol.html


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

/// DEFAULT LANGUAGE FILE.
require_once(DIR_FAQ_LANG . OSFDB_DEFAULT_LANG . '/faq_map.lang.php');

require(DIR_FAQ_INCLUDES . 'FaqSeo.php');
require_once(DIR_FAQ_INCLUDES . 'FaqFeedSM.php');
require_once(DIR_FAQ_INCLUDES . 'FaqArrayData.php');

//for test runs
$sm_time_start = explode(' ', microtime());

$seoFunc = new FaqSeo();
$sitemap = new FaqFeedSM();// gets reset for append runs
$fmbData = new FaqArrayData();

//DEBUG: echo '<pre>';print_r($_POST);echo '</pre>';//exit();





$fmbData->output_to_file = false;
$fmbData->append_to_file = false;

$fmbData->smfilename = 'sitemap';//default
$fmbData->filePath = '';

$fmbData->smfilenameIdx = 'sitemap_index';//default
$fmbData->filePathIdx = '';

$smExtensionIdx = '.xml';//will change when appending to a gz index file
$outputFunctionIdx = 'outputDocument';//will change when appending to a gz index file

$notify_url = '';
$smFileNames = array();
$c = 0;
$i = 1;

//validation arrays
$ouputTypes = array('norm','gzip','auto');
$appendTypes = array('sitemap','sitemap_index');
$smapTypes = array('local','other','test');

// output method
$output_type = in_array($_POST['output_type'], $ouputTypes) ? $_POST['output_type'] : $ouputTypes[0];
$usegzip = ($output_type == 'gzip' || $output_type == 'auto') ? true : false;

// predicted entry count
$output_size = intval($_POST['output_size']);

// notify search engines
$notify = (isset($_POST['notify']) && $_POST['notify'] == 'ping') ? true : false;
$smap_type = in_array($_POST['smap'], $smapTypes) ? $_POST['smap'] : $smapTypes[0];


if ($smap_type != 'test') $fmbData->output_to_file = true;
$fmbData->append_to_file = ($smap_type == 'other') ? true : false;
$append_type = in_array($_POST['append_to'], $appendTypes) ? $_POST['append_to'] : $appendTypes[0];
$autogenerate = (($output_type == 'auto') || ($fmbData->append_to_file && $append_type=='sitemap_index')) ? true : false; //autogenerate sitemaps and index file

////DEBUG:
//echo '$output_type='.$output_type.'<br />';
//echo '$autogenerate='.($autogenerate ? 'true':'false').'<br />';
////exit();

$osfDBRes = $sqle->db_compile(TABLE_FAQ_ADMIN, array('key_value'), FaqSQLExt::$SELECT, "key_name='OSFA_SM_PATH'");
if(isset($_POST['smbase']) && FaqFuncs::not_null($_POST['smbase'])){
  $fmbData->filePath = db_input($_POST['smbase'], false);
}elseif(false !== ($osfDBArray = db_fetch_array($osfDBRes))){
  $fmbData->filePath = $osfDBArray['key_value'];
}
//$fmbData->filePath = (false !== ($osfDBArray = db_fetch_array($osfDBRes))) ? $osfDBArray['key_value'] : db_input($_POST['smbase'], false);

//set default path
//if($fmbData->filePath == DIR_FS_WEB_ROOT){
//  $fmbData->filePath = '../';
//}else{
//  $fnPathSplit = substr_count($fmbData->filePath, '/');
//
//  $fmbData->filePath = str_pad('/', (count($fnPathSplit)-1)*3, '../', STR_PAD_LEFT);
//}


//DEBUG: echo '<pre>';print_r($fmbData->get_array());echo '</pre>';//exit();

// validate the file
$smftype = false;
if($fmbData->append_to_file){
  if($append_type == 'sitemap'){
    if(!$seoFunc->validateSitemapFile($_POST['sitemap_name'])){
      //probably a hacking attempt. Bail out now.
      $messageHandler->addNext($_POST['sitemap_name'] . ' is not a valid sitemap. Try another file', FaqMessage::$error);
      FaqFuncs::redirect(FILE_FAQ_ADMIN . '?map=true&result=unknown&f=' . urlencode($_POST['sitemap_name']));
    }
    //we need the index path as well incase of large DBs
    //it needs to be built with our $fmbData->filePath var so we do this first
    $fmbData->filePathIdx = $fmbData->filePath . $fmbData->smfilenameIdx;
    $fmbData->filePathIdx = realpath($fmbData->filePathIdx.$smExtensionIdx);
    $fmbData->filePathIdx = substr($fmbData->filePathIdx, 0, -strlen($smExtensionIdx));

    $fmbData->filePath = $_POST['sitemap_name'];

    // open an existing document for editting
    $sitemap = new FaqFeedSM($fmbData->filePath);//file extension hasn't been removed yet

    $smftype = $seoFunc->getFileType($fmbData->filePath);
    if($smftype!=false){
      $fmbData->filePath = substr($fmbData->filePath, 0, -strlen($smftype));
    }

  }else{
    if(!$seoFunc->validateSitemapFile($_POST['sitemap_index_name'])){
      //probably a hacking attempt. Bail out now.
      $messageHandler->addNext($_POST['sitemap_index_name'] . ' is not a valid sitemap_index. Try another file', FaqMessage::$error);
      FaqFuncs::redirect(FILE_FAQ_ADMIN . '?map=true&result=unknown&f=' . urlencode($_POST['sitemap_index_name']));
    }
    $fmbData->filePathIdx = $_POST['sitemap_index_name'];

    $smftype = $seoFunc->getFileType($fmbData->filePathIdx);
    if($smftype!=false){
      $fmbData->filePathIdx = substr($fmbData->filePathIdx, 0, -strlen($smftype));
      $smExtensionIdx = $smftype;
      //we only compress index files if they are already compressed
      $outputFunctionIdx = ($smftype=='.xml.gz') ? 'outputGZDocument' : $outputFunctionIdx;
    }
    $usegzip = true;// always compress indexed sitemaps
    $fmbData->filePath .= $fmbData->smfilename . '_faq';//we need the sitemap path as well
  }

}else{
  $fmbData->filePathIdx = $fmbData->filePath . $fmbData->smfilenameIdx;//must be first
  $fmbData->filePath .= $fmbData->smfilename;
}


// file writters
if ($usegzip) {
  $outputFunction = 'outputGZDocument';
  $file_extension = '.xml.gz';
} else {
  $outputFunction = 'outputDocument';
  $file_extension = '.xml';
}


// save some settings
$sqle->db_compile(TABLE_FAQ_ADMIN, array('key_value' => $smap_type), FaqSQLExt::$UPDATE, "key_name='OSFA_SM_TYPE'");
switch($smap_type){
  case 'local':
  case 'other':
    $sqle->db_compile(TABLE_FAQ_ADMIN, array('key_value' => ($notify ? 'ping':'norm')), FaqSQLExt::$UPDATE, "key_name='OSFA_SM_NOTIFY'");
    break;
}


////DEBUG:
//echo '$fmbData->filePath='.$fmbData->filePath.'<br />';
//echo '$fmbData->filePathIdx='.$fmbData->filePathIdx.'<br />';
//echo '$usegzip='.($usegzip ? 'true':'false').'<br />';
//exit();

////////////////////////
// Start building output
////////////////////////

// map your osTicket home page
$osf_map_page = array('link' => $seoFunc->formatLink('index.php', '', 'SSL'),
                      'priority' => FaqFeedSM::$PRIORITY_HIGH,
                      'lastmod' => $seoFunc->iso8601_date(0),
                      'changefreq' => FaqFeedSM::$CHANGEFREQ_WEEKLY);
$sitemap->addContent($osf_map_page);
$c++;

// map your osFaq home page
$osf_map_page = array('link' => $seoFunc->formatLink(FILE_FAQ, 'print=true', 'SSL'),
                      'priority' => FaqFeedSM::$PRIORITY_MID,
                      'lastmod' => $seoFunc->iso8601_date(0),
                      'changefreq' => FaqFeedSM::$CHANGEFREQ_WEEKLY);
$sitemap->addContent($osf_map_page);
$c++;
$strlen = $sitemap->getDocumentSize();

/// map your osFAQ category pages
$catpages_query = db_query("SELECT id as cid, category as title, UNIX_TIMESTAMP(IFNULL(last_modified, date_added)) as lastmod FROM " . TABLE_FAQCATS . " WHERE category_status = '1' ORDER BY category");
if (db_num_rows($catpages_query) > 0) {
  while ($catpages = db_fetch_array($catpages_query)) {
    $osf_map_page = array('link' => $seoFunc->formatLink(FILE_FAQ, 'cid=' . (int)$catpages['cid'] . '&print=true', 'SSL', $catpages['title']),
                          'priority' => FaqFeedSM::$PRIORITY_LOW,
                          'lastmod' => $seoFunc->iso8601_date($catpages['lastmod']),
                          'changefreq' => FaqFeedSM::$CHANGEFREQ_MONTHLY);

    $sitemap->addContent($osf_map_page);
    $strlen = $sitemap->getDocumentSize();

    $c++;
    if ($autogenerate) {

//      //DEBUG:
//      echo '$strlen='.$strlen.'<br />';
//      echo '$c='.$c.'<br />';
//      echo 'FaqFeedSM::$MAX_ENTRYS='.FaqFeedSM::$MAX_ENTRYS.'<br />';


      // 50,000 entrys OR filesize > 10,480,000 bytes
      if ($c >= FaqFeedSM::$MAX_ENTRYS || $strlen >= FaqFeedSM::$MAX_SIZE) {

        if($fmbData->append_to_file && ($append_type == 'sitemap')){
          // close and save existing map document
          $sm_file_name = $fmbData->filePath . ($i==1 ? '': $i) . $file_extension;
          if($fmbData->output_to_file) $sitemap->$outputFunction($sm_file_name);
        }else{
          // close and save new map document
          $sm_file_name = $fmbData->filePath . $i . $file_extension;

          if($fmbData->output_to_file) $sitemap->$outputFunction($sm_file_name);
        }
        //convert to a url
        $sm_file_name = $seoFunc->getPublicUrl((false!==realpath($sm_file_name) ? realpath($sm_file_name):$sm_file_name . ' [NOTE: File does not exist]'));
        $smFileNames[] = $sm_file_name;

        // start a new map document
        $sitemap = new FaqFeedSM();

        $c = 0;
        $i++;
        $strlen = $sitemap->getDocumentSize();
      }
    }
  }
}


//DEBUG: echo '<pre>';print_r($fmbData->get_array());echo '</pre>';//exit();


if ($fmbData->output_to_file || $autogenerate) {

  // generates a sitemap_index file. This contains a list of all sitemaps in this set
  // only used for very large databases
  if (!$autogenerate && $i == 1){

    /* For append runs, URL already has leading part at this point */
    if($fmbData->append_to_file){
      $notify_url = $fmbData->filePath . $file_extension;
    }else{
      $notify_url = OSF_DOC_ROOT . $fmbData->filePath . $file_extension;
    }

    // save a single map document
    $sitemap->$outputFunction($notify_url);

    //DEBUG: echo '<pre>';print_r($notify_url);echo '</pre>';exit();

    $osfBaseName = basename($notify_url);
    $osfFilePath = substr($seoFunc->getPublicUrl($notify_url, true), 0, -strlen($osfBaseName));

    //DEBUG: echo '$notify_url='.$notify_url.PHP_EOL;


    $sqle->db_compile(TABLE_FAQ_ADMIN, array('key_value' => $osfFilePath), FaqSQLExt::$UPDATE, "key_name='OSFA_SM_PATH'");
    $sqle->db_compile(TABLE_FAQ_ADMIN, array('key_value' => $osfBaseName), FaqSQLExt::$UPDATE, "key_name='OSFA_SM_MAP'");

    //DEBUG: echo '<pre>';print_r($fmbData->get_array());echo '</pre>';exit();

  }elseif( $autogenerate && ( $i > 1 || ($fmbData->append_to_file && $append_type == 'sitemap_index') ) ){

    // close and save current map document
    $sm_file_name = $fmbData->filePath . $i . $file_extension;
    if($fmbData->output_to_file) $sitemap->$outputFunction($sm_file_name);

    $sm_file_name = $seoFunc->getPublicUrl((false!==realpath($sm_file_name) ? realpath($sm_file_name):$sm_file_name . ' [NOTE: File does not exist]'));
    $smFileNames[] = $sm_file_name;


    require_once(DIR_FAQ_INCLUDES . 'FaqFeedSMIdx.php');
    if($fmbData->append_to_file && ($append_type == 'sitemap_index')){
      // open an existing map-index document
      $sitemapIdx = new FaqFeedSMIdx($fmbData->filePathIdx . $smExtensionIdx);
    }else{
      // start a new map-index document
      $sitemapIdx = new FaqFeedSMIdx();
    }

    // populate the map-index document
    $smFileString = '';
    foreach ($smFileNames as $smfname) {
      $smIdxData = array('link' => htmlentities(utf8_encode($smfname)),
                               'lastmod' => $seoFunc->iso8601_date(0));
      $sitemapIdx->addContent($smIdxData);
      $smFileString = $smfname . ',';
    }

    if($fmbData->output_to_file){
      // save the the map-index document.
      // Indexed sitemaps use gzip, the index does not unless data is being appended to an existing gz
      $sitemapIdx->$outputFunctionIdx($fmbData->filePathIdx . $smExtensionIdx);
    }else{
      //notify user of files existence.
      $sitemapIdxStr = $seoFunc->getPublicUrl((realpath($fmbData->filePathIdx.$smExtensionIdx) ? realpath($fmbData->filePathIdx.$smExtensionIdx) : $fmbData->filePathIdx.$smExtensionIdx));
      $sitemapIdxStr = (false!==realpath($fmbData->filePathIdx.$smExtensionIdx) ? 'File Exists: '.$sitemapIdxStr : $sitemapIdxStr . ' [NOTE: File does not exist]') . "\n\n";
      $sitemapIdxStr .= $sitemapIdx->outputDocument();//for test runs
    }
    $notify_url = realpath($fmbData->filePathIdx . $smExtensionIdx);

    $sqle->db_compile(TABLE_FAQ_ADMIN, array('key_value' => $notify_url), FaqSQLExt::$UPDATE, "key_name='OSFA_SM_IDX'");
    $sqle->db_compile(TABLE_FAQ_ADMIN, array('key_value' => $smFileString), FaqSQLExt::$UPDATE, "key_name='OSFA_SM_IDX_MAPS'");

  }else{
    //TODO: give $notify_url a value!!
    $messageHandler->addNext(OSF_SITEMAP_ERROR_OUTPUT, FaqMessage::$error);
    FaqFuncs::redirect(FILE_FAQ_ADMIN . '?map=true&result=unknown&f=' . urlencode($notify_url));
  }

  if($fmbData->output_to_file){
    //dont notify if running on a testing server
    if ($notify && getenv('HTTP_HOST')!='localhost') {
      //notify google
      $result1 = 'Google&trade;: ' . strip_tags(fopen('http://www.google.com/webmasters/sitemaps/ping?sitemap=' . urlencode($notify_url), 'r'));
      //notify yahoo
      $result2 = 'Yahoo!&reg;: ' . strip_tags(fopen('http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=' . urlencode($notify_url), 'r'));
      //notify bing
      $result3 = 'Bing&trade;: ' . strip_tags(fopen('http://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode($notify_url), 'r'));
      //notify ask.com
      $result4 = 'Ask.com&trade;: ' . strip_tags(fopen('http://submissions.ask.com/ping?sitemap=' . urlencode($notify_url), 'r'));

      $messageHandler->addNext(OSF_SITEMAP_PING_RESULT, FaqMessage::$plain);
      $messageHandler->addNext($result1, FaqMessage::$success);
      $messageHandler->addNext($result2, FaqMessage::$success);
      $messageHandler->addNext($result3, FaqMessage::$success);
      $messageHandler->addNext($result4, FaqMessage::$success);
      FaqFuncs::redirect(FILE_FAQ_ADMIN . '?map=true&result=auto&f=' . urlencode($notify_url));
    }else{
      FaqFuncs::redirect(FILE_FAQ_ADMIN . '?map=true&result='. $smap_type . '&f=' . urlencode($notify_url));
    }
  }
}

// test runs
if(!$fmbData->output_to_file){
  echo '<h1>' . OSF_SITEMAP_RESULTS . '</h1>';


  if($i > 1){ //sitemap_index
    echo '<p>' . sprintf(OSF_INDEX_TEST, $i) . '</p>';
  }else{ //sitemap
    echo '<p>' . OSF_MAP_TEST . '</p>';
  }

  //generation stats
  $sm_time_end = explode(' ', microtime());
  $sm_parse_time = number_format(($sm_time_end[1] + $sm_time_end[0] - ($sm_time_start[1] + $sm_time_start[0])), 4);
  echo '<b>' . OSF_STATISTICS . '</b><br />';
  echo sprintf(OSF_ESTIMATED_RUNTIME, $sm_parse_time) . '<br />';
  if (function_exists('memory_get_usage')) echo(OSF_MEMORY_USAGE . FaqFuncs::display_filesize(memory_get_usage()) . '<br />');
  echo $output_size . OSF_URLS_WRITTEN . '<br /><br />';

  //test results
  echo '<a href="' . FaqFuncs::format_url(FILE_FAQ_ADMIN, 'map=true', 'SSL') . '">&laquo;&laquo; ' . OSF_SITEMAP_GENERATOR . '</a><br />';
  echo '<div align="center" style="background-color:#cccccc;padding:20px 0 20px 0;"><textarea cols="100" rows="40" readonly="readonly" style="background-color:#FFFFE6;">';
  echo $sitemap->outputDocument();
  echo "\n{$sitemapIdxStr}";
  echo '</textarea></div>';
}
?>