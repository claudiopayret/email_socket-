<?php

/**
 * Created by PhpStorm.
 * User: cpayret
 * Date: 12/2/2015
 * Time: 11:29 AM
 */

/**
 * All initial Var declarations
 */

   $debug_app = false; /** developer test error_reporting*/
   $execution_time_var_seg=600; /** 300 second 5 min */
   $memory_var_seg='64M'; /**in Mbytes */
   date_default_timezone_set(America/New_York);

if ($debug_app){
   error_reporting(E_ALL);
  ini_set('display_errors', 1);
}

ini_set('max_execution_time', $execution_time_var_seg);
ini_set('memory_limit',$memory_var_seg);

/**  * execution var * */
$sender = 'cpayret@jwkorth.com';







