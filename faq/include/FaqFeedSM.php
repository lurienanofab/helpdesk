<?php
/* *************************************************************************
  Id: FaqFeedSM.php

  Used to build xml Sitemap files for search engine bots.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

require_once('FaqFeed.php');// abstract implementation

/**
 * Used to build xml Sitemap files for search engine bots.
 * Also holds some static vars specified in the sitemap standards.
 * See: http://www.sitemaps.org/protocol.php
 *
 * @author Tim Gall
 *
 */
class FaqFeedSM extends FaqFeed{

	/* change frequency.
	 * NOTE: For backward compatibility we use "public static".
	 * In actuality these should be "const" (php >= 5.3.0)
	 * or "public static final" (php >= 6.0.0)
	 */
	public static $CHANGEFREQ_ALWAYS = 'always';
	public static $CHANGEFREQ_HOURLY = 'hourly';
	public static $CHANGEFREQ_DAILY = 'daily';
	public static $CHANGEFREQ_WEEKLY = 'weekly';
	public static $CHANGEFREQ_MONTHLY = 'monthly';
	public static $CHANGEFREQ_YEARLY = 'yearly';
	public static $CHANGEFREQ_NEVER = 'never';

	/* indexing priority in relation to other entries in the same sitemap */
	public static $PRIORITY_LOW = '1.0';
	public static $PRIORITY_MID = '0.5';
	public static $PRIORITY_HIGH = '0.0';

	/* Recommended size limits before a sitemap_index file is required.
	 * This means multiple sitemap files are created (each with a different name)
	 * and referenced in the sitemap_index file. */
	public static $MAX_ENTRYS = 50000;// url entries per sitemap
	public static $MAX_SIZE = 10480000;// bytes per sitemap


	/**
	 * Initialises a Sitemap xml document for adding content to.
	 * @return void
	 */
	function __construct($existingSitemapUrl = ''){
		if($existingSitemapUrl != ''){
			$this->resetDocument();
			if(strripos($existingSitemapUrl, '.gz')){
				$loaded = $this->document->load(gzdecode(file_get_contents(realpath($existingSitemapUrl), FILE_BINARY)));
			}else{
				$loaded = $this->document->load(realpath($existingSitemapUrl));
			}
		}else{
			$this->createDocument();
		}
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

		$root_node = $this->document->createElement('urlset');
		$root_node->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$this->document->appendChild($root_node);
	}

	/**
	 * Adds data element trees to a Sitemap document from a label indexed array.
	 * Required fields are:
	 * -link
	 * -priority
	 * -lastmod
	 * -changefreq
	 *
	 * @see /faq/include/FaqFeed#addContent($dataSource)
	 */
	public function addContent($labeledArray){

		$url = $this->document->createElement('url');

		$loc = $this->document->createElement('loc', $labeledArray['link']);
		$priority = $this->document->createElement('priority', $labeledArray['priority']);
		$lastmod = $this->document->createElement('lastmod', $labeledArray['lastmod']);
		$changefreq = $this->document->createElement('changefreq', $labeledArray['changefreq']);

		$url->appendChild($loc);
		$url->appendChild($priority);
		$url->appendChild($lastmod);
		$url->appendChild($changefreq);

		$this->document->getElementsByTagName('urlset')->item(0)->appendChild($url);
	}
}
?>