<?php
/* *************************************************************************
 Id: FaqFeedAtom.php

 Used to build rss feeds for xml readers on external websites/client software.
 This class outputs Atom v1.0 documents.


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
class FaqFeedAtom extends FaqFeed{

  /**
   * Initialises an Atom xml document for adding content to.
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

    $root_node = $this->document->createElement('feed');
    $root_node->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');

    $titleData = $this->document->createCDATASection(SERVER_DOMAIN . ' FAQs');
    $title = $this->document->createElement('title');
    $title->appendChild($titleData);
    $title->setAttribute('type', 'text');

    $link = $this->document->createElement('link');
    $link->setAttribute('rel', 'self');
    $link->setAttribute('type', 'application/atom+xml');
    $link->setAttribute('href', HTTP_SERVER . DIR_FS_WEB_ROOT . FILE_FAQ_FEED);

    $updated = $this->document->createElement('updated', date('c', time()));

    $rights = $this->document->createElement('rights', 'Copyright (c) ' . date('Y', time()) . ', ' . SERVER_DOMAIN . ', All Rights Reserved.');

    $generator = $this->document->createElement('generator', 'osFaq Generator');
    $generator->setAttribute('version', FAQ_VERSION);

    $root_node->appendChild($title);
    $root_node->appendChild($link);
    $root_node->appendChild($updated);
    $root_node->appendChild($rights);
    $root_node->appendChild($generator);

    $this->document->appendChild($root_node);
  }

  /**
   * Adds data element trees to the Atom document from an SQL Resultset.
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
      $authorInput = $this->cleanContent($ext_faqs['author_name'], true);

      $summaryInput = strip_tags($contentInput);
      $summaryInput = (strlen($summaryInput) > 200) ? substr($summaryInput, 0, 197) . '...' : $summaryInput;

      $updatedInput = intval($ext_faqs['updated'], 10);
      $id = intval($ext_faqs['id'], 10);
      $cid = intval($ext_faqs['cid'], 10);

      if($id > 0){
        $url = FaqFuncs::format_url(FILE_FAQ, 'cid=' . $cid . '&answer=' . $id, 'SSL', $titleInput) . '#f'.$id;
      }else{
        $url = FaqFuncs::format_url(FILE_FAQ, 'cid=' . $cid, 'SSL', $titleInput);
      }

      $entry = $this->document->createElement('entry');

      $id = $this->document->createElement('id', htmlentities($url));

      $titleData = $this->document->createCDATASection($titleInput);
      $title = $this->document->createElement('title');
      $title->appendChild($titleData);
      $title->setAttribute('type', 'text');

      $updated = $this->document->createElement('updated', date('c', $updatedInput));

      $link = $this->document->createElement('link');
      $link->setAttribute('rel', 'alternate');
      $link->setAttribute('href', $url);

      $summaryData = $this->document->createCDATASection($summaryInput);
      $summary = $this->document->createElement('summary');
      $summary->appendChild($summaryData);
      $summary->setAttribute('type', 'html');

      $contentData = $this->document->createCDATASection($contentInput);
      $content = $this->document->createElement('content');
      $content->appendChild($contentData);
      $content->setAttribute('type', 'html');

      //authors name
      if(FaqFuncs::not_null($authorInput)){
        $author = $this->document->createElement('author');
        $authorName = $this->document->createElement('name', $authorInput);
        $author->appendChild($authorName);

        $entry->appendChild($author);
      }

      $entry->appendChild($id);
      $entry->appendChild($title);
      $entry->appendChild($summary);
      $entry->appendChild($updated);
      $entry->appendChild($link);
      $entry->appendChild($content);

      //faster than DomXPath for this purpose
      $this->document->getElementsByTagName('feed')->item(0)->appendChild($entry);
    }
  }
}
?>