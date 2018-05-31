<?php
/* *************************************************************************
 Id: faq_feed.php

 RSS feed generator for osFaq. Output is in standard Atom v1.0 or RSS v2.0 format.
 Settings can be changed in admin to control the output of this system.

 SUGGESTIONS FOR USE:
 Place an RSS link to this page on a remote website.
 Standard Feed icons can be found here: http://www.feedicons.com/
 or
 Use an xml document reader to parse the content into a suitable format
 for your purposes. EG: the parser would be on the remote website and can be in any programming language.


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

//if feeds are turned off in admin
if(OSFDB_DISABLE_CLIENT=='true' || OSFDB_FEED_ALLOW=='false'){
  FaqFuncs::redirect(FILE_FAQ);
}


/* if caching is enabled, refresh the cached copy if its expired
 * then display the cached version.
 * if the cached version doesn't exist, we show the live version instead
 */
define('OSF_FEED_FILE', './faq/feeds/faq_feed.xml');
$osf_live = false;
$osf_rebuild = false;

if(OSFDB_FEED_CACH=='true'){
  if((false !== ($osfFeedPath = realpath(OSF_FEED_FILE)))){
    if(is_writable($osfFeedPath)){
      //workaround for new installs
      if(filesize($osfFeedPath)==0){
        $osf_rebuild = true;
      }else{
        $osf_rebuild = ( (mktime() - filemtime($osfFeedPath)) > (int)OSFDB_FEED_CACHE_LIMIT );
      }
    }else{
      $osf_live = true;
    }
  }else{
    $osf_live = true;
  }
}else{
  $osf_live = true;
}


// build the output if necessary
if($osf_rebuild || $osf_live){

  if(OSFDB_FEED_ATOM=='true'){
    //Atom v1.0
    require_once(DIR_FAQ_INCLUDES . 'FaqFeedAtom.php');
    $feedDoc = new FaqFeedAtom();
  }else{
    //RSS v2.0
    require_once(DIR_FAQ_INCLUDES . 'FaqFeedRSS.php');
    $feedDoc = new FaqFeedRSS();
  }

  //data filter
  $osf_orderBy = '';
  if(OSFDB_FEED_FEATURED=='true'){
    $osf_orderBy .= ' f.show_on_nonfaq DESC,';
  }
  if(OSFDB_FEED_VIEWS=='true'){
    $osf_orderBy .= ' f.client_views DESC,';
  }
  if(OSFDB_FEED_DATE=='true'){
    $osf_orderBy .= ' updated DESC,';
  }
  //order by rand() has a lot of server overhead attached, particularly with large databases
  if(OSFDB_FEED_RANDOM=='true'){
    $osf_orderBy .= ' rand(),';
  }

  if(strlen($osf_orderBy) > 0){
    $osf_orderBy = ' order by' . substr($osf_orderBy, 0, -1);
  }

  //data
  $osf_limit = (OSFDB_FEED_CATEGORIES=='true') ? OSFDB_FEED_LIMIT/2 : OSFDB_FEED_LIMIT;
  $sqlResultset = db_query("SELECT distinct f.id, f2f.faqcategory_id as cid, f.question as title, f.answer as content, f.name as author_name, UNIX_TIMESTAMP(IFNULL(f.last_modified, f.date_added)) as updated FROM ".TABLE_FAQS." f, ".TABLE_FAQS2FAQCATS." f2f LEFT JOIN ".TABLE_FAQCATS." fc ON((f2f.faqcategory_id = 0) OR (f2f.faqcategory_id = fc.id AND fc.category_status = 1)) WHERE f2f.faq_id = f.id AND f.faq_active = 1 group by f.question" . $osf_orderBy . " LIMIT 0,".((int)$osf_limit).";");

  $feedDoc->addContent($sqlResultset);

  if(OSFDB_FEED_CATEGORIES=='true'){
    /// categories last
    $sqlResultsetCats = db_query("SELECT f.id as cid, f.category as title, f.category as content, UNIX_TIMESTAMP(IFNULL(f.last_modified, f.date_added)) as updated, (SELECT COUNT(f2fc1.faq_id) FROM ".TABLE_FAQS2FAQCATS." f2fc1, ".TABLE_FAQCATS." fc1, ".TABLE_FAQS." f1 WHERE f.id = fc1.id AND fc1.id = f2fc1.faqcategory_id AND f2fc1.faq_id = f1.id AND f1.faq_active = 1) AS fcount FROM ".TABLE_FAQCATS." f WHERE f.category_status = 1" . $osf_orderBy . " LIMIT 0,".((int)$osf_limit).";");
    $feedDoc->addContent($sqlResultsetCats);
  }

  //output
  if(OSFDB_FEED_CACH=='true' && !$osf_live){
    //cache output
    $feedDoc->outputDocument(OSF_FEED_FILE);
  }else{
    //live output
    echo $feedDoc->outputDocument();
  }
}

if(OSFDB_FEED_CACH=='true' && !$osf_live){
  //if(OSFDB_FEED_ATOM=='true'){
  //  header('Content-Type: application/x.atom+xml; charset=utf-8;');
  //}else{
  //  header('Content-Type: application/rss+xml; charset=utf-8;');
  //}
  echo file_get_contents(OSF_FEED_FILE, FILE_TEXT);
}
?>