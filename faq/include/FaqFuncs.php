<?php
/* *************************************************************************
 Id: FaqFuncs.php

 Miscellaneous functions frequently used by client and admin FAQ pages.


 Tim Gall
 Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
 http://osfaq.oz-devworx.com.au

 This file is part of osFaq.

 Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
 For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

 ************************************************************************* */

class FaqFuncs{

  /**
   * Convenience function for get_all_get_params($exclude, true)
   *
   * @param string[] $exclude
   * @return string
   */
  public static function get_all_get_params_hidden($exclude = array()) {
    return FaqFuncs::get_all_get_params($exclude, true);
  }

  /**
   * get_all_get_params()
   *
   * @param string $exclude
   * @param boolean $hidden_fields if true a string with hidden fields is returned
   * else a string with formatted get params is returned
   * @return
   */
  public static function get_all_get_params($exclude = array(), $hidden_fields = false) {
    $params = '';
    reset($_GET);
    foreach ($_GET as $key => $value) {
      if (FaqFuncs::not_null($key) && FaqFuncs::not_null($value) && !in_array($key, $exclude)){
        if($hidden_fields)
        $params .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
        else
        $params .= $key.'='.$value.'&';
      }
    }
    return $params;
  }

  /**
   * get_all_post_params()
   *
   * @param string $exclude
   * @param boolean $as_get if true a string with formatted get params is returned
   * else a string with hidden fields is returned
   * @return
   */
  public static function get_all_post_params($exclude = array(), $as_get = false) {
    $params = '';
    reset($_POST);
    foreach ($_POST as $key => $value) {
      if (FaqFuncs::not_null($key) && FaqFuncs::not_null($value) && !in_array($key, $exclude)){
        if($as_get)
        $params .= $key.'='.$value.'&';
        else
        $params .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
      }
    }
    return $params;
  }

  /**
   * Uses: date()
   * Accepts these formats:
   * YYYY-MM-DD
   * DD-MM-YYYY
   * YYYY-MM-DD HH:II:SS
   * DD-MM-YYYY HH:II:SS
   *
   * @param mixed $raw_datetime
   * @param string $format_mask for use with php date()
   * @return A formatted string representing the given date in the specified format
   */
  public static function format_date($raw_datetime, $format_mask = OSF_DATE_FMT_STD) {

    if (($raw_datetime == '0000-00-00 00:00:00') || ($raw_datetime == '')) return false;
    $dt = FaqFuncs::get_split_date_time($raw_datetime);
    return date($format_mask, mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']));
  }

  /**
   * Attempts to determine the layout of the date and applies the appropriate
   * splitting technique.
   * This is a helper function for other date formatting functions but may be used directly if required.
   *
   * Accepts these formats:
   * YYYY-MM-DD
   * DD-MM-YYYY
   * YYYY-MM-DD HH:II:SS
   * DD-MM-YYYY HH:II:SS
   *
   * @param mixed $raw_datetime. Formatted as specified above.
   * @return
   * an array of numeric date-time parts in the form of: $key=>$val;
   * with $key names: year, month, day, hour, minute, second
   */
  public static function get_split_date_time($raw_datetime) {
    if (strpos($raw_datetime, "-") < 4) {
      $split_date_time = array('day' => (int)substr($raw_datetime, 0, 2), 'month' => (int)substr($raw_datetime, 3, 2), 'year' => (int)substr($raw_datetime, 6, 4), 'hour' => (int)substr($raw_datetime, 11, 2), 'minute' => (int)substr($raw_datetime, 14, 2), 'second' => (int)substr($raw_datetime, 17, 2));
    } else {
      $split_date_time = array('year' => (int)substr($raw_datetime, 0, 4), 'month' => (int)substr($raw_datetime, 5, 2), 'day' => (int)substr($raw_datetime, 8, 2), 'hour' => (int)substr($raw_datetime, 11, 2), 'minute' => (int)substr($raw_datetime, 14, 2), 'second' => (int)substr($raw_datetime, 17, 2));
    }
    return $split_date_time;
  }

  /**
   * not_null()
   *
   * @param mixed $value. A value to check for data
   * @return true if $value is not null or empty (whitespace is not considered a value)
   */
  public static function not_null($value) {
    if (is_array($value) && (count($value) > 0)) {
      return true;
    } elseif ( is_numeric($value) || (is_string($value) && ($value != '') && (strtoupper($value) != 'NULL') && (strlen(trim($value)) > 0)) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * output_string()
   *
   * @param mixed $string
   * @param bool $sanitise. If true, certain characters will be parsed to html chars. simmilar to htmlspecialchars
   * @return
   */
  public static function output_string($string, $sanitise = true) {
    //print_r($string.PHP_EOL);
    if ($sanitise == true) {
      $output_string = strtr($string, array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;'));
    } else {
      $output_string = $string;
    }

    return trim($output_string);
  }

  /**
   * Convert a utf8 string to ascii (iso-8559-1) or the other way around
   * by using the revers parameter. This function uses iconv if available
   * otherwise falls-back to mb_convert_encoding or utf8_encode/decode.
   *
   * @param string $string - the string to convert
   * @param boolean $reverse - if true, conversion will be ascii to utf8
   */
  public static function utf8_to_ascii($string, $reverse=false){
    if(function_exists('iconv')){
      if($reverse){
        return iconv('ISO-8859-1//IGNORE//TRANSLIT', 'UTF-8', $string);
      }else{
        return iconv('UTF-8', 'ISO-8859-1//IGNORE//TRANSLIT', $string);
      }
    }elseif(function_exists('mb_convert_encoding')){
      if($reverse){
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
      }else{
        return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
      }
    }else{
      if($reverse){
        return utf8_encode($string);
      }else{
        return utf8_decode($string);
      }
    }
  }

  /**
   * Redirect to another page or site
   *
   * @param mixed $url
   * @return
   */
  public static function redirect($url) {

    if ((OSFDB_ENABLE_SSL == 'true') && (getenv('HTTPS') == 'on')) {
      if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) {
        $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER));
      }
    }

    FaqFuncs::clean_redirect($url);
  }

  /**
   * format_url()
   *
   * @param string $page. The page being requested.
   * @param string $parameters. url $_GET parameters
   * @param string $connection. Type of connection state. SSL = https, anything else is http
   * @param string $seo_name. To increase performance when SEO URLs are turned on, set this when requesting the URL.
   * Otherwise the seo page name will be retrived in a seperate sql query.
   * @param mixed $root_ws_dir
   * @param $force_std_url - Over-ride for the SEO url function if it encouters a url it cant work with.
   * @return a formatted URL. EG: https://somedomain.ext/somefile
   */
  public static function format_url($page = '', $parameters = '', $connection = 'SSL', $seo_name = '', $root_ws_dir = DIR_FS_WEB_ROOT, $force_std_url=false) {

    // change to admin dir for admin requests
    if (stripos(OSF_PHP_SELF, DIR_PATH_ADMIN) > -1) {
      $root_ws_dir = DIR_WS_ADMIN;
    }

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

    // no seo links for admin requests
    if(($root_ws_dir != DIR_WS_ADMIN) && OSFDB_URL_FRIENDLY=='true' && FaqFuncs::not_null($parameters) && !$force_std_url){
      // build a SEO URL. Force standard mode for urls it can work with
      $pagename = FaqFuncs::format_seo_page_url($parameters, $seo_name);
      if($pagename==null){
        return FaqFuncs::format_url($page, $parameters, $connection, $seo_name, $root_ws_dir, true);
      }
      $link .= $pagename;

    }else{
      // avoid double trailing slash
      if ($page == '/') $page = '';

      // build a Standard URL
      $link .= basename($page) . (FaqFuncs::not_null($parameters) ? '?' . FaqFuncs::output_string($parameters) : '');
    }

    //return htmlspecialchars(utf8_encode($link));
    return utf8_encode($link);
  }

  /**
   * Format the page and parameter part of a seo url.
   * EG: foobar-c4-a98-pg2-i50-p.html
   * The c portion always exists.
   * The a, pg, i and p params are optional.
   * Anything else gets appended to the end as $_GET params.
   *
   * @param mixed $parameters url parameters
   * @param string $seo_name Human readable name
   * @return pagename part of a url strictly formatted as "$category . $answer . PAGINATION VARS . $print"
   * If one of the parts doesnt exist it is omitted from the url
   */
  public static function format_seo_page_url($parameters, $seo_name = ''){
    $trailing_params = '';
    $print = '';
    $answer = '';
    $category = '';
    $pg_i = '';
    $id = 0;


    $seo_params = explode('&', $parameters);
    foreach($seo_params as $value){
      $params = explode('=', $value);
      if(FaqFuncs::not_null($params[0]) && FaqFuncs::not_null($params[1])){
        switch($params[0]){
          case 'cid':
            $category = '-c' . (int)$params[1];
            // dont over-write an answer id
            if($id==0) $id = (int)$params[1];
            break;

          case 'answer':
            $answer = '-a' . (int)$params[1];
            $id = (int)$params[1];
            break;

          case 'pg':
          case 'i':
            $pg_i .= '-' . $params[0] . (int)$params[1];
            break;

          case 'print':
            if($params[1]=='true') $print = '-p';
            break;

          default:
            $trailing_params .= '&' . $params[0] . '=' . $params[1];
            break;
        }
      }
    } ///foreach

    // set descriptive text for the url
    if(FaqFuncs::not_null($seo_name)){
      $url_text = $seo_name;
    }elseif($id==0){
      // we cant build this url correctly. The url will be handled by the format_url function.
      return null;
    }else{
      if(FaqFuncs::not_null($answer)){
        $url_text = FaqFuncs::get_question($id);
      }else{
        $url_text = FaqFuncs::get_cat_name($id);
      }
    }

    // make the text "url friendly"
    $url_text = FaqFuncs::prepare_link_text($url_text);


    // build a SEO URL
    return $url_text . $category . $answer . $pg_i . $print . '.html' . (FaqFuncs::not_null($trailing_params) ? '?' . substr($trailing_params, 1) : '');
  }

  /**
   * HTML image
   *
   * @param mixed $src
   * @param string $alt
   * @param string $width
   * @param string $height
   * @param string $params
   * @return string. a html image tag
   */
  public static function format_image($src, $alt = '', $width = '', $height = '', $params = '') {
    $image = '<img src="' . $src . '" border="0" alt="' . $alt . '"';
    if ($alt) {
      $image .= ' title=" ' . $alt . ' "';
    }
    if ($width) {
      $image .= ' width="' . $width . '"';
    }
    if ($height) {
      $image .= ' height="' . $height . '"';
    }
    if ($params) {
      $image .= ' ' . $params;
    }
    $image .= ' />';
    return $image;
  }

  /**
   * collect all faq categories
   *
   * @param string $parent_id
   * @param string $spacing
   * @param string $exclude
   * @param string $faq_tree_array
   * @param bool $include_itself
   * @param string $url
   * @return
   */
  public static function faq_get_tree($parent_id = '0', $spacing = '', $exclude = '', $faq_tree_array = '', $include_itself = false, $url = 'faq.php') {
    if (!is_array($faq_tree_array)) $faq_tree_array = array();
    if ($include_itself) {
      $faq_category_query = db_query("select id, category, (SELECT COUNT(f2fc.faq_id) FROM " . TABLE_FAQS2FAQCATS . " f2fc, " . TABLE_FAQCATS . " fc, " . TABLE_FAQS . " f WHERE fc.id = " . (int)$parent_id . " AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = '1') AS fcount from " . TABLE_FAQCATS . " where id = '" . (int)$parent_id . "' and category_status = '1'");
      $faq_category = db_fetch_array($faq_category_query);

      $lnk = FaqFuncs::format_url($url, 'cid='.$parent_id, 'SSL', $faq_category['category']);
      $faq_tree_array[] = array('text' => '&bull; <a href="' . $lnk . '" class="faq">' . $faq_category['category'] . '</a>' . ' (' . $faq_category['fcount'] . ')', 'link' => $lnk, 'title' => $faq_category['category'] . ' (' . $faq_category['fcount'] . ')');
    }

    $faq_category_query = db_query("select f1.id, f1.category, (SELECT COUNT(f2fc.faq_id) FROM " . TABLE_FAQS2FAQCATS . " f2fc, " . TABLE_FAQCATS . " fc, " . TABLE_FAQS . " f WHERE f1.id = fc.id AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = '1') AS fcount from " . TABLE_FAQCATS . " f1 where f1.parent_id = '" . (int)$parent_id . "' and f1.category_status = '1' order by f1.category");
    while ($faq_category = db_fetch_array($faq_category_query)) {
      if ($exclude != $faq_category['id']){
        $lnk = FaqFuncs::format_url($url, 'cid='.$faq_category['id'], 'SSL', $faq_category['category']);

        $faq_tree_array[] = array('text' => $spacing . '&bull; <a href="' . $lnk . '" class="faq">' . $faq_category['category'] . '</a>' . ' (' . $faq_category['fcount'] . ')', 'link' => $lnk, 'title' => $faq_category['category'] . ' (' . $faq_category['fcount'] . ')');
      }
      $faq_tree_array = FaqFuncs::faq_get_tree($faq_category['id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $faq_tree_array);
    }
    return $faq_tree_array;
  }

  /**
   * collect faq parent categories for $category_id
   *
   * @param string $category_id
   * @param string $spacing
   * @param string $exclude
   * @param string $faq_tree_array
   * @param string $url
   * @return
   */
  public static function faq_get_parent_tree($category_id = '0', $spacing = '', $exclude = '', $faq_tree_array = '', $url = 'faq.php') {
    if (!is_array($faq_tree_array)) $faq_tree_array = array();

    $faq_category_query = db_query("select parent_id from " . TABLE_FAQCATS . " where id = '" . (int)$category_id . "' and parent_id <> '0' and category_status = '1' order by category");
    while ($faq_category = db_fetch_array($faq_category_query)) {

      $faq_parent_query = db_query("select f1.id, f1.parent_id, f1.category, (SELECT COUNT(f2fc.faq_id) FROM " . TABLE_FAQS2FAQCATS . " f2fc, " . TABLE_FAQCATS . " fc, " . TABLE_FAQS . " f WHERE f1.id = fc.id AND fc.id = f2fc.faqcategory_id AND f2fc.faq_id = f.id AND f.faq_active = '1') AS fcount from " . TABLE_FAQCATS . " f1 where f1.id = '" . (int)$faq_category['parent_id'] . "' and f1.category_status = '1' order by f1.category");
      if (db_num_rows($faq_parent_query) > 0) {
        $faq_parent = db_fetch_array($faq_parent_query);

        if ($exclude != $faq_parent['id']) {
          $cidstr = ($url == 'faq.php') ? 'cid' : 'fcPath';
          $lnk = FaqFuncs::format_url($url, $cidstr.'='.$faq_parent['id'], 'SSL', $faq_parent['category']);

          $faq_tree_array[] = array('text' => $spacing . '&bull; <a href="' . $lnk . '" class="faq">' . $faq_parent['category'] . '</a>' . ' (' . $faq_parent['fcount'] . ')',
                                  'link' => $lnk,
                                  'title' => $faq_parent['category'] . ' (' . $faq_parent['fcount'] . ')');


        }
        $faq_tree_array = FaqFuncs::faq_get_parent_tree($faq_parent['parent_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $faq_tree_array);
      }

    }

    $faq_parent_array = array();
    for ($i = 0; $i < sizeof($faq_tree_array); $i++) {
      $faq_parent_array[] = $faq_tree_array[$i];
    }
    return $faq_parent_array;
  }

  /**
   * Highlight keywords that are not part of a html tag
   *
   * @param mixed $search_result to search for keywords
   * @param mixed $keywords to highlight
   * @return string - $search_result with any $keywords higlighted using html
   */
  public static function highlight_keywords($search_result, $keywords) {
    $highlight_text = $search_result;
    if (FaqFuncs::not_null($keywords)) {

      $search = "@(?!<.[^>]+)(" . $keywords . ")(?!.[^<]+>)@is";

      $replace = '<span style="background-color:' . OSFDB_SEARCH_BG_COLOR . ';">' . "\\1" . '</span>';

      $highlight_text = preg_replace($search, $replace, $search_result);
    }
    return $highlight_text;
  }

  /**
   * Found this function in the php5 manuals user comments. Many thanks to the author.
   * Since it seems fairly flawless as it is I merely copied the funtions straight out of the manual.
   *
   * @param mixed $filesize as an integer, in bytes.
   * @return A nicely rounded filesize to the most appropriate scale with scale indicator.
   * EG: Byte, KB, MB, GB, TB, PB
   */
  public static function display_filesize($filesize){
    if(is_numeric($filesize)){
      $decr = 1024; $step = 0;
      $prefix = array('Byte','KB','MB','GB','TB','PB');

      while(($filesize / $decr) > 0.9){
        $filesize = $filesize / $decr;
        $step++;
      }
      return round($filesize,2).' '.$prefix[$step];
    } else {
      return 'NaN';
    }
  }

  /**
   * get_question()
   *
   * @param mixed $faq_id
   * @return
   */
  public static function get_question($faq_id) {
    $faq_query = db_query("select question from ".TABLE_FAQS." where id = '" . (int)$faq_id . "'");
    $faq = db_fetch_array($faq_query);
    return $faq['question'];
  }

  /**
   * get_answer()
   *
   * @param mixed $faq_id
   * @return
   */
  public static function get_answer($faq_id) {
    $faq_query = db_query("select answer from ".TABLE_FAQS." where id = '" . (int)$faq_id . "'");
    $faq = db_fetch_array($faq_query);
    return $faq['answer'];
  }

  public static function get_cat_name($cat_id){
    $faq_category_query = db_query("select category from ".TABLE_FAQCATS." where id = " . (int)$cat_id . ";");
    if ($faq_category = db_fetch_array($faq_category_query)) {
      return $faq_category['category'];
    }
    return 'Unknown Category';
  }

  /**
   * Intended for internal use by the redirect functions.
   *
   * @param mixed $url must be a complete URL
   * @return void
   */
  private static function clean_redirect($url){
    ob_clean();
    ob_end_clean();

    header('Status: 200');
    header('Location: ' . $url);
    exit(0);
  }

  /**
   * Remove non-seo words and sanitise for url's.
   * Trim the text if its too long.
   *
   * @param mixed $link_text. Text for use in a URL
   * @return A SEO URL friendly string
   */
  private static function prepare_link_text($link_text) {
    // remove non-seo words and sanitise for use in a URL
    $raw_string = FaqFuncs::clean_seo_content($link_text);
    // trim the text if its too long
    $raw_string = (strlen($raw_string) <= OSFDB_MAX_URL_LENGTH) ? $raw_string : substr($raw_string, 0, OSFDB_MAX_URL_LENGTH) . '...';
    // replace trailing -
    return (strrpos($raw_string, '-') == strlen($raw_string)-1) ? substr($raw_string, 0, -1) : $raw_string;
  }

  /**
   * Strip out non SEO friendly stuff from a string.
   * Mainly for URLs and meta-tags, etc.
   *
   * @param mixed $raw_string
   * @return string
   */
  private static function clean_seo_content($raw_string) {

    // strip out common html
    $entry_text = strip_tags($raw_string);

    // strip out non compliant text and any remaining code
    $search = array(
    '@<script[^>]*?>.*?</script>@si', // javascript
    '@<[\/\!]*?[^<>]*?>@si', // HTML tags
    '@([\r\n])[\s]+@', // white space
    '@&(nbsp|#160);@i', // hard space
    '@&([a-z]|#[0-9a-f]);@Ui', // special symbols
    '@(\'|"|`)@U' // quotes
    );

    $replace = array('', '', '', '-', '', '');

    // strip out common english joining words
    if(OSFDB_SEO_REMOVE_JOINERS=='true'){
      $search[] = '@( |-)(is the|of a|and a|or a|or|and|it|on|at|a|the|is|of)( |-)@si'; // joining words
      $replace[] = '-';
    }

    // strip out anything else that may cause problems or formatting inconsitencies
    $search[] = '@( - |\/|\?|&|\(|\)|\[|\]|\{|\}|\+|\@|;|:|\.| |--)+@si';
    $replace[] = '-';


    $clean_string = preg_replace($search, $replace, $entry_text);

    return $clean_string;
  }
}
?>