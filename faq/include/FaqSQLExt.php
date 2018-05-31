<?php
/* *************************************************************************
  Id: FaqSQLExt.php

  Convenience class for compiling and executing DDL and DML sql queries
  from an array of data.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

/**
 * Simple FaqSQLExt
 *
 * @package Faqs
 * @author Tim Gall
 * @copyright 2009
 * @version $Id$
 * @access public
 */
class FaqSQLExt {

  public static $INSERT = 'insert';
  public static $UPDATE = 'update';
  public static $SELECT = 'select';

  /**
   * Convenience class for compiling and executing DDL and DML sql queries
   * from an array of data. Can be: insert, select or update
   *
   * For select queries, column aliases and subqueries can be used
   * in the main query and queries condition;
   * BUT you must use the column name and alias as a single array entry.
   * Same with subqueries and thier alias.
   *
   * Inserts and updates cannot accept subqueries or aliases in the main query
   * but in the queries condition it should work.
   *
   * @param string $table. The table to perform the query on. Only accepts one
   * @param array $data. An object array in the form $key=>$val for update and insert; $key only for select's.
   * @param string $action. Can be 'insert', 'update' or 'select' (case insensitive)
   * @param string $condition. Query conditions
   * @param string $group_by. group by list as a string
   * @param string $sort_order. order by list as a string
   * @param string $limit. EG: 25,50 (where 25 is the first result and 50 is the last result)
   * @return mixed An sql query resultset. For insert and update this will be the number of affected rows or true/false.
   */
  public function db_compile($table, $data, $action = 'insert', $condition = '', $group_by = '', $sort_order = '', $limit = '') {
    reset($data);
    $action = strtolower($action);

    switch($action){

    case FaqSQLExt::$SELECT:
      $query = 'select ';
      foreach ($data as $column) {
        $query .= $column . ', ';
      }
      $query = substr($query, 0, -2) . ' from ' . $table;
      break;


    case FaqSQLExt::$UPDATE:
      $query = 'update ' . $table . ' set ';
      foreach ($data as $column => $value) {
        switch ((string )$value) {
          case 'now()':
            $query .= $column . ' = now()';
            break;
          case 'null':
            $query .= $column .= ' = null';
            break;
          default:
            $query .= $column . ' = ' . db_input($value);
            break;
        }
        $query .= ', ';
      }
      $query = substr($query, 0, -2);
      break;


    case FaqSQLExt::$INSERT:
    default:
      $query = 'insert into ' . $table . ' (';
      $queryVals = ') values (';

      foreach ($data as $column => $value) {
        // columns
        $query .= $column . ', ';

        // data
        switch (strtolower((string)$value)) {
          case 'now()':
            $queryVals .= 'now()';
            break;
          case 'null':
            $queryVals .= 'null';
            break;
          default:
            $queryVals .= db_input($value);
            break;
        }
        $queryVals .= ', ';
      }
      $query = substr($query, 0, -2) . substr($queryVals, 0, -2) . ')';
      break;
    }

    // add any conditions to query string
    if($action != 'insert' && FaqFuncs::not_null($condition)){
      $query .= ' where ' . $condition;
    }

    // add these options to select queries only
    if($action == 'select'){
      if(FaqFuncs::not_null($group_by)) $query .= ' group by ' . $group_by;

      if(FaqFuncs::not_null($sort_order)) $query .= ' order by ' . $sort_order;

      if(FaqFuncs::not_null($limit)) $query .= ' LIMIT ' . $limit;
    }

    return db_query($query . ';');
  }
}
?>