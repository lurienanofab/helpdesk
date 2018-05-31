<?php
/* *************************************************************************
  Id: FaqFeed.php

  Basic abstract constructs for utf-8 xml based document generation.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

/**
 * Basic constructs for utf-8 xml based document generation.
 * Implementors only need to implement 1 abstract method FaqFeed::addContent($dataSource).
 * The dataSource can be whatever suits your application.
 *
 * @author Tim Gall
 */
abstract class FaqFeed {
	/**
	 * A DomDocument. All nodes are nodes of this document
	 * @var DomDocument
	 */
	protected $document;

	/**
	 * Add content from a datasource into the DomDocument.
	 * This is the only method you need to implement to use this class.
	 * @param $dataSource. A valid datasource. Can be whatever you want.
	 * @return void
	 */
	abstract public function addContent($dataSource);

	/**
	 * If $fileName is not set, output returns.
	 * Else the file is saved as $fileName and the script returns the filesize.
	 * @param $fileName. Can be empty or (relative or absolute filename)
	 * @return string. An xml document if $fileName is not set otherwise the filesize.
	 */
	public function outputDocument($fileName = ''){
		//$this->document->normalizeDocument();
		if($fileName == ''){
			return $this->document->saveXML();
		}else{
			try{
				return $this->document->save($fileName) . OSF_BYTES_WRITTEN;
			}catch(Exception $e){
				return OSF_ERROR_FILE_NOT_SAVED;
			}
		}
	}

	/**
	 * If $fileName is not set, output returns.
	 * Else the file is saved as $fileName and the script returns the filesize.
	 * @param $fileName. Can be empty or (relative or absolute filename)
	 * @return string. A GZiped xml document if $fileName is not set; otherwise the filesize.
	 */
	public function outputGZDocument($fileName = ''){
		//$this->document->normalizeDocument();
		if($fileName == ''){
			return gzencode($this->document->saveXML(), 6, FORCE_GZIP);
		}else{
			try{
				$binData = gzencode($this->document->saveXML(), 6, FORCE_GZIP);
				return file_put_contents($fileName, $binData, FILE_BINARY) . OSF_BYTES_WRITTEN;
			}catch(Exception $e){
				return OSF_ERROR_FILE_NOT_SAVED;
			}
		}
	}

	/**
	 *
	 * @return int. The size of this document in bytes
	 */
	public function getDocumentSize(){
		return strlen($this->document->saveXML());
	}

	/**
	 * Initialises the DomDocument Object.
	 * @return void
	 */
	protected function resetDocument(){
		$this->document = new DomDocument('1.0', 'utf-8');
		$this->document->formatOutput = true;
	}

	protected function cleanContent($raw_string, $removeTags = false) {
		// remove script elements
		$clean_string = preg_replace('@<script[^>]*?>.*?</script>@si', '', $raw_string);
		if($removeTags){
		  $clean_string = strip_tags($clean_string);
		}

		return trim($clean_string);
	}
}
?>