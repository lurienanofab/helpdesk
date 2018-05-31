<?php
/* *************************************************************************
  Id: FaqFeedSMIdx.php

  Used to build Sitemap Index files for search engine bots.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

require_once('FaqFeed.php');// abstract implementation

/**
 * Used to build Sitemap Index files for search engine bots.
 *
 * @author Tim Gall
 *
 */
class FaqFeedSMIdx extends FaqFeed{

	/**
	 * Initialises a Sitemap xml document for adding content to.
	 * @return void
	 */
	function __construct($existingSitemapUrl = ''){
		if($existingSitemapUrl != ''){
			$this->resetDocument();
			if(strripos($existingSitemapUrl, '.gz')){

				if(!is_file($existingSitemapUrl)){
					if(is_file(realpath($existingSitemapUrl))){
						$existingSitemapUrl = realpath($existingSitemapUrl);
					}else{
						exit('ERROR: Filepath error in FaqFeedSMIdx::__construct. Contact support for help.');
					}
				}

				$loaded = $this->document->load(gzdecode(file_get_contents($existingSitemapUrl, FILE_BINARY)));
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

		$root_node = $this->document->createElement('sitemapindex');
		$root_node->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$this->document->appendChild($root_node);
	}

	/**
	 * Adds data element trees to a Sitemap Index document from a label indexed array.
	 * Required fields are:
	 * -link
	 * -lastmod
	 *
	 * @see /faq/include/FaqFeed#addContent($dataSource)
	 */
	public function addContent($labeledArray){

		$sitemap = $this->document->createElement('sitemap');

		$loc = $this->document->createElement('loc', $labeledArray['link']);
		$lastmod = $this->document->createElement('lastmod', $labeledArray['lastmod']);

		$sitemap->appendChild($loc);
		$sitemap->appendChild($lastmod);

		$this->document->getElementsByTagName('sitemapindex')->item(0)->appendChild($sitemap);
	}
}
?>