<?php
/* *************************************************************************
  Id: FaqPaginator.php

  Provides pagination abilities to the client and admin FAQ pages.
  Supports the osFaq URL's system.

  Added rel="noindex" params to URLs to control SE Indexing bots
  from indexing duplicate content.
  REASON: duplicate content doesn't benefit a sites position in search results.

  Based on:
  http://www.catchmyfame.com/2007/07/28/finally-the-simple-pagination-class/

  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqPaginator {
  var $items_per_page;
  var $items_total;
  var $current_page;
  var $num_pages;
  var $mid_range;
  var $low;
  var $high;
  var $limit;
  var $return;
  var $default_ipp;
  var $querystring;
  var $parent_page;

  /**
   * FaqPaginator::__construct()
   *
   * @param string $parent_page The page this paginator is interacting with
   * @return void
   */
  function __construct($parent_page='') {
    $this->current_page = 1;
    $this->mid_range = 5;
    $this->default_ipp = (FaqFuncs::not_null(OSFDB_DEFAULT_IPP) && (int)OSFDB_DEFAULT_IPP > 0) ? (int)OSFDB_DEFAULT_IPP : 10;
    $this->items_per_page = $this->default_ipp;

    if(!FaqFuncs::not_null($parent_page)){
      $parent_page = DIR_FS_WEB_ROOT . basename(OSF_PHP_SELF);
    }
    $this->parent_page = $parent_page;
  }


  /**
   * Use this function to set the items_total if you need to use
   * any of the pagination vars in your code before you call paginate()
   *
   * @param mixed $items_total
   * @return void
   */
  public function set_items_total($item_total){
    $this->items_total = $item_total;
    $this->set_page_vars();
  }

  /**
   * Build a pagination set consisting of limit vars and a numeric pagination link strip.
   *
   * @return void
   */
  public function paginate() {

    $this->set_page_vars();
    $this->set_querystring();

    $prev_page = $this->current_page - 1;
    $next_page = $this->current_page + 1;

    // Page %s of %s
    $this->return = '<span class="paginate">' . sprintf(OSF_PAGINATOR_PAGE_OF, $this->current_page, $this->num_pages) . '</span> - ';


    if ($this->items_total > $this->items_per_page) {

      $this->return .= ($this->current_page != 1 && $this->items_total >= $this->items_per_page) ? '<a class="paginate" rel="nofollow" href="' . FaqFuncs::format_url($this->parent_page, $this->querystring.'pg='.$prev_page, 'SSL') . '">'.OSF_PAGINATOR_PREVIOUS.'</a> ' : '<span class="inactive">'.OSF_PAGINATOR_PREVIOUS.'</span> ';

      $this->start_range = intval($this->current_page - floor($this->mid_range / 2));
      $this->end_range = intval($this->current_page + floor($this->mid_range / 2));

      if ($this->start_range <= 0) {
        $this->end_range += abs($this->start_range) + 1;
        $this->start_range = 1;
      }
      if ($this->end_range > $this->num_pages) {
        $this->start_range -= $this->end_range - $this->num_pages;
        $this->end_range = $this->num_pages;
      }
      $this->range = range($this->start_range, $this->end_range);

      for ($i = 1; $i <= $this->num_pages; $i++) {
        if ($this->range[0] > 2 && $i == $this->range[0])
          $this->return .= " ... ";
        // loop through all pages. if first, last, or in range, display them
        if ($i == 1 || $i == $this->num_pages || in_array($i, $this->range)) {
          $this->return .= ( ($i == $this->current_page) && ($_GET['i'] != OSF_PAGINATOR_ALL) ) ? '<a class="current" rel="nofollow" title="'.OSF_PAGINATOR_CURRENT.'" href="#">'.$i.'</a> ' : '<a class="paginate" title="'.sprintf(OSF_PAGINATOR_GOTO, $i, $this->num_pages).'" href="'.FaqFuncs::format_url($this->parent_page, $this->querystring.'pg='.$i, 'SSL').'">'.$i.'</a> ';
        }
        if ($this->range[$this->mid_range - 1] < $this->num_pages - 1 && $i == $this->range[$this->mid_range - 1])
          $this->return .= " ... ";
      }
      $this->return .= (($this->current_page != $this->num_pages && $this->items_total >= $this->items_per_page) && ($_GET['i'] != OSF_PAGINATOR_ALL)) ? '<a class="paginate" rel="nofollow" href="'.FaqFuncs::format_url($this->parent_page, $this->querystring.'pg='.$next_page, 'SSL').'">'.OSF_PAGINATOR_NEXT.'</a>' : '<span class="inactive">'.OSF_PAGINATOR_NEXT.'</span>';
      $this->return .= ($_GET['i'] == OSF_PAGINATOR_ALL) ? '<a class="current" rel="nofollow" title="'.OSF_PAGINATOR_CURRENT.'" href="#" style="margin-left:10px">'.OSF_PAGINATOR_ALL.'</a> ' : '<a class="paginate" rel="nofollow" style="margin-left:10px" href="'.FaqFuncs::format_url($this->parent_page, $this->querystring.'pg=1&ipp='.OSF_PAGINATOR_ALL, 'SSL').'">'.OSF_PAGINATOR_ALL.'</a> ';
    }
    $this->low = ($this->current_page - 1) * $this->items_per_page;
    $this->high = ($_GET['i'] == OSF_PAGINATOR_ALL) ? $this->items_total : $this->items_per_page;
    $this->limit = ($_GET['i'] == OSF_PAGINATOR_ALL) ? '' : ' LIMIT '.$this->low.','.$this->high;
  }

  /**
   * Return an items per page jump menu
   *
   * @return a html jump menu as text
   */
  public function display_items_per_page() {
    $items = '';

    if($this->default_ipp < 10){
      // allow for values below 10
      $ipp_array = array($this->default_ipp, 10, 25, 50, 100);
    }elseif($this->default_ipp > 100){
      // allow for values above 100
      $ipp_array = array(10, 25, 50, 100, $this->default_ipp);
    }else{
      $ipp_array = array(10, 25, 50, 100);
    }
    $ipp_array[] = OSF_PAGINATOR_ALL;

    foreach ($ipp_array as $ipp_opt)
      $items .= ($ipp_opt == $_GET['i']) ? '<option selected="selected" value="'.$ipp_opt.'">'.$ipp_opt.'</option>'."\n" : '<option value="'.$ipp_opt.'">'.$ipp_opt.'</option>'."\n";

    return '<span class="paginate">'.OSF_PAGINATOR_ITEMS.'</span><select class="paginate" rel="nofollow" onchange="window.location=\''.FaqFuncs::format_url($this->parent_page, $this->querystring.'ipp=\'+this[this.selectedIndex].value', 'SSL').';return false;">'.$items.'</select> '."\n";
  }

  /**
   * Return a page list jump menu
   *
   * @return a html jump menu as text
   */
  public function display_jump_menu() {
    if (($this->num_pages <= 1) || ($_GET['i'] == OSF_PAGINATOR_ALL)) {
      return null;
    }
    for ($i = 1; $i <= $this->num_pages; $i++) {
      $option .= ($i == $this->current_page) ? '<option value="'.$i.'" selected="selected">'.$i.'</option>'."\n" : '<option value="'.$i.'">'.$i.'</option>'."\n";
    }
    return '<span class="paginate">'.OSF_PAGINATOR_PAGE.'</span><select class="paginate" rel="nofollow" onchange="window.location=\''.FaqFuncs::format_url($this->parent_page, $this->querystring.'page=\'+this[this.selectedIndex].value', 'SSL').';return false;">'.$option.'</select> '."\n";
  }

  /**
   * Return the page number buttons used for pagination
   *
   * @return a pagination collection as a string.
   */
  public function display_pages() {
    return $this->return;
  }



  /* ************************
  * PRIVATE FUNCTIONS
  ************************* */
  /**
   * Set some page related variables.
   *
   * @return void
   */
  private function set_page_vars(){
    // workaround for drop menus and 'All' options
    if(isset($_GET['ipp'])) $_GET['i'] = $_GET['ipp'];
    if(isset($_GET['page'])) $_GET['pg'] = $_GET['page'];

    // retain users page limit for the session
    if(isset($_GET['i'])){
      $_SESSION['ipp']=($_GET['i']==OSF_PAGINATOR_ALL) ? OSF_PAGINATOR_ALL : (int)$_GET['i'];
    }elseif(isset($_SESSION['ipp'])){
      $_GET['i']=$_SESSION['ipp'];
    }

    $this->current_page = (int)$_GET['pg']; // must be numeric
    $this->items_per_page = (int)(FaqFuncs::not_null($_GET['i'])) ? $_GET['i'] : $this->items_per_page; // must be numeric

    if($_GET['i']==OSF_PAGINATOR_ALL){
      $this->items_per_page = $this->items_total;
      $this->num_pages = 1;
    }elseif(!is_numeric($this->items_per_page) || $this->items_per_page <= 0){
      $this->items_per_page = $this->default_ipp;
      $this->num_pages = ceil($this->items_total / $this->items_per_page);
    }else{
      $this->num_pages = ceil($this->items_total / $this->items_per_page);
    }

    if ($this->current_page < 1 or !is_numeric($this->current_page))
      $this->current_page = 1;
    if ($this->current_page > $this->num_pages)
      $this->current_page = $this->num_pages;
  }

  /**
   * Set the url $_GET param string used for generated urls in this class
   *
   * @return void
   */
  private function set_querystring(){
    $this->querystring = '';

    $exclude = array('pg','page','i','ipp');

    if (isset($_GET)) {
      $this->querystring .= FaqFuncs::get_all_get_params($exclude);
    }

    if (isset($_POST)) {
      $pparams = FaqFuncs::get_all_post_params($exclude, true);

      if(FaqFuncs::not_null($pparams)){
        $this->querystring .= (FaqFuncs::not_null($this->querystring) ? '&':'') . $pparams;
      }
    }
  }
}
?>