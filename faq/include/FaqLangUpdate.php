<?php
/* *************************************************************************
  Id: FaqLangUpdate.php

  Handles dynamically updating system langugage translations in the database.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqLangUpdate{

  private function __construct(){

  }

  /**
   * Make sure at least one language is installed.
   */
  public static function dbLangCheck(){

    //count language keys in settings_lang table
    $lang_rows_query = db_query("select count(language) as lang_rows from " . TABLE_FAQ_SETTINGS_LANG . " where language='".OSFDB_DEFAULT_LANG."';");
    $lang_rows_data = db_fetch_array($lang_rows_query);

    // if there arent any, install english if it isnt already
    if($lang_rows_data['lang_rows']==0){
      $fallback_query = db_query("select count(language) as lang_rows from " . TABLE_FAQ_SETTINGS_LANG . " where language='english';");
      $fallback_data = db_fetch_array($fallback_query);
      if($fallback_data['lang_rows']==0){
        self::updateDbLang('english');
      }
    }
  }


  /**
   * Updates language definitions in the database for the osFaq internal systems;
   * Performs some verification methods on the SQL file before inserting.
   * If errors are found this function will attempt to be reasonably precise
   * as to the nature of the problem; including reporting missing or duplicate keys,
   * incorrect language column entries and SQL syntax errors.
   * The previous language will be restored if any errors are encountered.
   *
   * @param string $lang_name - the languages name thats being updated
   * @param string $lang_directory - the directory to read from
   * (allows this function to be called from any location in the file structure)
   */
  public static function updateDbLang($lang_name, $lang_directory=DIR_FAQ_LANG){
    global $messageHandler;

    $lang_sql = $lang_directory . $lang_name . '/faq_settings_lang.sql';
    $lang_sql_errors = false;
    $lang_backup = null;

    /// Loadup SQL schema file
    if (!file_exists($lang_sql) || !($schema = file_get_contents($lang_sql))) {
      $messageHandler->addNext(sprintf(OSF_LANG_NOT_FOUND, $lang_directory . $lang_name), FaqMessage::$warning);
    }else{

      if (!empty($schema)) {
        //count insert rows in file
        $insertRows = substr_count($schema, "('OSF");
        $queries = explode(';', str_replace('%TABLE_PREFIX%', TABLE_PREFIX, $schema));

        //count unique keys in settings table
        $max_rows_query = db_query("select count(key_name) as setting_rows from " . TABLE_FAQ_SETTINGS . ";");
        $max_rows_data = db_fetch_array($max_rows_query);

        if($max_rows_data['setting_rows']!=$insertRows){

          // Create a list of missing or duplicate keys and compile in a message
          $key_message = '';
          $key_verify_query = db_query("select key_name from " . TABLE_FAQ_SETTINGS . ";");
          while($key_verify_data = db_fetch_array($key_verify_query)){
            $key_count = substr_count($schema, "'{$key_verify_data['key_name']}'");

            if($key_count > 1){
              $key_message .= $key_verify_data['key_name'] . ' (' . OSF_LANG_DUPLICATE . ')<br />';
            }elseif($key_count < 1){
              $key_message .= $key_verify_data['key_name'] . ' (' . OSF_LANG_MISSING . ')<br />';
            }
          }
          $messageHandler->addNext(sprintf(OSF_LANG_ROW_MISMATCH, $max_rows_data['setting_rows'], $insertRows) . '<br />' . $key_message, FaqMessage::$warning);
          $lang_sql_errors = true;

        }elseif( (substr_count($schema, "'{$lang_name}'") < $insertRows) ) {
          $messageHandler->addNext(OSF_LANG_MISMATCH, FaqMessage::$warning);
          $lang_sql_errors = true;
        }else{

          if (!empty($queries) && count($queries) > 0) {

            //backup old translation and remove from db
            $lang_backup = self::getOldLang($lang_name);
            self::deleteOldLang($lang_name);

            ///insert new translation
            $allowed_queries = array('CREATE TABLE IF NOT EXISTS', 'INSERT INTO');
            foreach ($queries as $sql) {
              $allowed_to_send = false;
              $insSql = trim($sql);
              if (!empty($insSql)) {
                foreach($allowed_queries as $allowed_q){
                  if(strcmp($allowed_q, substr($insSql, 0, strlen($allowed_q)))==0){
                    $allowed_to_send = true;
                    break;
                  }
                }

                // invalid query types detected
                if (!$allowed_to_send){
                  $messageHandler->addNext(sprintf(OSF_LANG_SCHEMA_IILEGAL, $allowed_queries[0], $allowed_queries[1]), FaqMessage::$error);
                  $lang_sql_errors = true;
                  // break out on errors
                  break;
                }elseif ($allowed_to_send && !db_query($insSql . ';')) {
                  $messageHandler->addNext(OSF_LANG_SQL_ERROR, FaqMessage::$error);
                  $lang_sql_errors = true;
                  // break out on errors
                  break;
                }
              }
            }
            if(!$lang_sql_errors) $messageHandler->addNext(OSF_LANG_UPDATED, FaqMessage::$success);

          } else {
            $messageHandler->addNext(OSF_LANG_SCHEMA_BAD, FaqMessage::$error);
            $lang_sql_errors = true;
          }
        }
      } else {
        $messageHandler->addNext(OSF_LANG_SCHEMA_EMPTY, FaqMessage::$error);
        $lang_sql_errors = true;
      }
    }
    //if errors occurred, restore the db with its original data (if any).
    //english is the fallback language
    if($lang_sql_errors){
      self::restoreOldLang($lang_backup);
    }
  }


  public static function updateAllDbLangs($lang_directory=DIR_FAQ_LANG){

    $langs = array();
    $langs_dir = dir($lang_directory);

    while (false !== ($lang_file = $langs_dir->read())) {
      if ( (substr($lang_file, 0, 1)!='.') && (substr($lang_file, 0, 1)!='_') && is_dir($lang_directory . $lang_file) ){
        $langs[] = $lang_file;
      }
    }
    $langs_dir->close();

    foreach($langs as $lang){
      self::updateDbLang($lang);
    }
  }


  private static function getOldLang($lang_name){
    //backup
    $lang_data = array();
    $lang_backup_query = db_query("select settings_key, language, title, description, last_modified from " . TABLE_FAQ_SETTINGS_LANG . " where language='{$lang_name}';");
    while($lang_backup = db_fetch_array($lang_backup_query))
      $lang_data[] = $lang_backup;
    return $lang_data;
  }


  private static function restoreOldLang($lang_data){
    if($lang_data==null) return;

    //cleanup
    self::deleteOldLang($lang_data[0]['language']);
    //restore
    for($i=0, $n=count($lang_data); $i<$n; $i++)
      db_query("insert into " . TABLE_FAQ_SETTINGS_LANG . " (settings_key, language, title, description, last_modified) values ('{$lang_data[$i]['settings_key']}', '{$lang_data[$i]['language']}', '{$lang_data[$i]['title']}', '{$lang_data[$i]['description']}', '{$lang_data[$i]['last_modified']}');");
  }


  private static function deleteOldLang($lang_name){
    return db_query("delete from " . TABLE_FAQ_SETTINGS_LANG . " where language='{$lang_name}';");
  }
}
?>