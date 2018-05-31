<?php
/* *************************************************************************
 Id: FaqSeo.php

 A collection of funtions used by the xml sitemapper pages.


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

class FaqSeo{

	function __construct(){
    //nothing todo here at the moment.
	}

	function formatLink($page = '', $parameters = '', $connection = '', $seo_name = '', $root_ws_dir = DIR_FS_WEB_ROOT, $force_std_url = false) {

		// build the directory portion of the url
		switch ($connection) {
			case 'SSL':
				if (OSFDB_ENABLE_SSL == 'true') {
					$link = HTTPS_SERVER . $root_ws_dir;
					break;
				}
			case 'NONSSL':
			default:
				$link = HTTP_SERVER . $root_ws_dir;
				break;
		}

		// build page and param portions of the url
		if (OSFDB_URL_FRIENDLY == 'true' && FaqFuncs::not_null($parameters) && !$force_std_url) {
			// build a SEO URL. Recurse in standard mode for non SEO urls
			$pagename = FaqFuncs::format_seo_page_url($parameters, $seo_name);
			if($pagename==null){
				return $this->formatLink($page, $parameters, $connection, $seo_name, $root_ws_dir, true);
			}
			$link .= $pagename;

		} else {
			// avoid double trailing slash
			if ($page == '/') $page = '';

			// build a Standard URL
			$link .= basename($page) . (FaqFuncs::not_null($parameters) ? '?' . FaqFuncs::output_string($parameters) : '');
		}

		return htmlspecialchars(utf8_encode($link));
		//return utf8_encode($link);
	}

	function iso8601_date($timestamp) {
		$timestamp = ($timestamp <> 0) ? $timestamp : time();
		return date('c', $timestamp);
	}

	function get_real_name($output_type){
		$real_name = '';
		switch ($output_type){
			case 'auto':
				$real_name = 'multiple gzip compressed xml with uncompressed index file';
				break;
			case 'gzip':
				$real_name = 'gzip compressed xml';
				break;
			case 'norm':
			default:
				$real_name = 'xml';
				break;
		}
		return $real_name;
	}

	/**
	 * Get a multidimensional array of all valid sitemap and sitemap_index files.
	 * This is done by scanning all directories and subdirectories in your public root dir.
	 * Only files with .xml or .xml.gz extension are considered.
	 * The xml doc elements are then examined to see if the files are valid sitemap or sitemap_index files.
	 * Only valid matches are returned (sorted by type. EG: sitemap and sitemap_index).
	 *
	 * @return multidimensional array
	 */
	function findSitemapFiles($filePath){
		$path = realpath($filePath);
		$sitemaps = array();
		$sitemapIndexes = array();
		$domDoc = new DomDocument('1.0', 'utf-8');

		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach($objects as $name => $object){
			if(false !== strripos($name, '.xml')){

				if(false !== strripos($name, '.gz')){
					$domDoc->loadXML(gzdecode(file_get_contents($name, FILE_BINARY)));
				}else{
					$domDoc->load($name);
				}

				// valid sitemap
				$len = $domDoc->getElementsByTagName('urlset')->length;
				if($len > 0 && $len < 50000){
					$sitemaps[] = array('id' => $name, 'text' => $name . ' [' . $domDoc->getElementsByTagName('url')->length . ' entries, ' . (is_writable($name) ? 'writable' : 'not-writable') . ']');
				}
				// valid sitemap_index
				$len = $domDoc->getElementsByTagName('sitemapindex')->length;
				if($len > 0 && $len < 50000){
					$sitemapIndexes[] = array('id' => $name, 'text' => $name . ' [' . $domDoc->getElementsByTagName('sitemap')->length . ' entries, ' . (is_writable($name) ? 'writable' : 'not-writable') . ']');
				}
			}
		}
		return array('sitemap' => $sitemaps, 'sitemap_index' => $sitemapIndexes);
	}

	/**
	 * Is this file a sitemap or sitemap_index.
	 * @param string $absoluteUrl
	 * @return boolean if $absoluteUrl is a vaild sitemap or sitemap_index.
	 */
	function validateSitemapFile($absoluteUrl){
		$domDoc = new DomDocument('1.0', 'utf-8');

		if(false !== strripos($absoluteUrl, '.gz')){
			$isValid = $domDoc->loadXML(gzdecode(file_get_contents($absoluteUrl, FILE_BINARY)));
		}else{
			$isValid = $domDoc->load($absoluteUrl);
		}

		if($isValid){
			$isValid = (($domDoc->getElementsByTagName('urlset')->length > 0) || ($domDoc->getElementsByTagName('sitemapindex')->length > 0));
		}

		return $isValid;
	}

	function getPublicUrl($fileName, $shortenUrl=false){
		//fix windows seperators
		if(false !== strpos($fileName, "\\")){
      $publicUrl = preg_replace('/(\\\\+)/', '/', $fileName);
    }else{
      $publicUrl = $fileName;
    }

		if($shortenUrl){
			//remove private portion of filepath
			$publicUrl = str_replace(OSF_DOC_ROOT, '', $publicUrl);
		}else{
			$publicUrl = str_replace(OSF_DOC_ROOT, HTTP_SERVER, $publicUrl);
		}

		if($publicUrl=='') $publicUrl = '/';

		return utf8_encode($publicUrl);
	}

	/**
	 * Is file and Is file writable
	 * @param string $absoluteFilePath Must be a filepath, not a url
	 * @return string
	 */
	function getFileState($absoluteFilePath){
		return ' [' . (is_file($absoluteFilePath) ? OSF_EXISTS . ', ' . (is_writable($absoluteFilePath) ? OSF_WRITABLE:OSF_NOT_WRITABLE) : OSF_NOT_EXIST) . ']';
	}

	/**
	 * Looks for xml and xml.gz extensions
	 * @param string $filePath Can be a filepath or URL
	 * @return mixed boolean false if filetype is neither, else the extension string is returned (including leading dot)
	 */
	function getFileType($filePath){
		$foundType = false;
		if(preg_match('/.*(\.xml\.gz)/i',$filePath)>0){
			$foundType = '.xml.gz';
		}elseif(preg_match('/.*(\.xml)/i',$filePath)>0){
			$foundType = '.xml';
		}
		return $foundType;
	}
}
?>