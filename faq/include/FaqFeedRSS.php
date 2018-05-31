<?php
/* *************************************************************************
 Id: FaqFeedRSS.php

 Used to build rss feeds for xml readers on external websites/client software.
 This class outputs RSS v2.0 documents.


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

require_once('FaqFeed.php');// abstract implementation

/**
 * Used to build rss feeds for xml readers on external websites/client software.
 *
 * @author Tim Gall
 *
 */
class FaqFeedRSS extends FaqFeed{

  /**
   * Initialises an RSS 2.0 xml document for adding content to.
   * @return void
   */
  function __construct(){
    $this->createDocument();
  }

  function __destruct(){
    unset($this->document);
  }

  /**
   *
   * @return void
   */
  private function createDocument(){
    $this->resetDocument();

    $root_node = $this->document->createElement('rss');
    $root_node->setAttribute('version', '2.0');
    $root_node->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');

    $title = $this->document->createElement('title', SERVER_DOMAIN . ' FAQs');

    $link = $this->document->createElement('link', HTTP_SERVER . DIR_FS_WEB_ROOT);

    $updated = $this->document->createElement('lastBuildDate', date('r', time()));

    $generator = $this->document->createElement('generator', 'osFaq Generator/' . FAQ_VERSION);

    $channel = $this->document->createElement('channel');

    $root_node->appendChild($title);
    $root_node->appendChild($link);
    $root_node->appendChild($updated);
    $root_node->appendChild($generator);
    $root_node->appendChild($channel);

    $this->document->appendChild($root_node);
  }

  /**
   * Adds data element trees to the RSS 2.0 document from an SQL Resultset.
   * Required fields are:
   * -title
   * -content
   * -cid //category id
   * -id  //faq id
   * @see /faq/include/FaqFeed#addContent($dataSource)
   */
  public function addContent($sqlResultset){

    while($ext_faqs=db_fetch_array($sqlResultset)){

      $titleInput = $this->cleanContent($ext_faqs['title'], true);

      $contentInput = $this->cleanContent($ext_faqs['content'] . (empty($ext_faqs['fcount']) ? '' : ' (contains '.$ext_faqs['fcount'].' FAQs)'));
      $authorInput = strip_tags($this->cleanContent($ext_faqs['author_name']));

      $summaryInput = strip_tags($contentInput);//rss is not so good with html/xhtml
      $summaryInput = (strlen($summaryInput) > 500) ? substr($summaryInput, 0, 497) . '...' : $summaryInput;

      $updatedInput = intval($ext_faqs['updated'], 10);
      $id = intval($ext_faqs['id'], 10);
      $cid = intval($ext_faqs['cid'], 10);

      if($id > 0){
        $url = FaqFuncs::format_url(FILE_FAQ, 'cid=' . $cid . '&answer=' . $id, 'SSL', $titleInput) . '#f'.$id;
      }else{
        $url = FaqFuncs::format_url(FILE_FAQ, 'cid=' . $cid, 'SSL', $titleInput);
      }

      $entry = $this->document->createElement('item');

      $title = $this->document->createElement('title', $titleInput);

      $updated = $this->document->createElement('pubDate', date('r', $updatedInput));

      $link = $this->document->createElement('link', htmlentities($url));

      $content = $this->document->createElement('description', $summaryInput);

      //authors name
      if(FaqFuncs::not_null($authorInput)){
        $author = $this->document->createElement('dc:creator', $authorInput);

        $entry->appendChild($author);
      }

      $entry->appendChild($title);
      $entry->appendChild($updated);
      $entry->appendChild($link);
      $entry->appendChild($content);

      //faster than DomXPath for this purpose
      $this->document->getElementsByTagName('channel')->item(0)->appendChild($entry);
    }
  }
}
?>