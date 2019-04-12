<?php
/**
 * Created by PhpStorm.
 * User: cpayret
 * Date: 12/2/2015
 * Time: 11:29 AM
 */

/*
include_once('init.php');
include_once('DB_connect_I.php');
require_once('smtp_validateEmail.php');

*/
require_once('/var/www/secure.jwkorth/admintest/socket/init.php');
require_once('/var/www/secure.jwkorth/admintest/socket/DB_connect_I.php');
require_once('/var/www/secure.jwkorth/admintest/socket/smtp_validateEmail.php');



if (isset($argv[1])){ //Coming from crontab
    $Performance_Mode=$argv[1];
    $Number_Records=$argv[2];
    $Threads=$argv[3];
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') { //Coming from browser
    $Performance_Mode = (isset($_GET["Performance_Mode"]) ? $_GET["Performance_Mode"] : 1);
    $Number_Records = (isset($_GET["Number_Records"]) ? $_GET["Number_Records"] : 3);
    $Threads = (isset($_GET["Threads"]) ? $_GET["Threads"] : 1);
}
$initial=time(); //initial time
echo @date('H:i:s', time()).'<br>';
$DB_layer=new DB_connect_I();
$SMTP_Validator = new SMTP_validateEmail();
$SMTP_Validator->debug = false; //debug instance
$count_result = array();
/**
Performance Mode
 *   Mode 1 : FASTEST  : only port 25 , SMTP_validateEmail->max_conn_time = 10; //default 30 , SMTP_validateEmail->max_read_time = 2 ; //default 5
 *   Mode 2 : FAST  : only port 25 , SMTP_validateEmail->max_conn_time = 20; //default 30 , SMTP_validateEmail->max_read_time = 3 ; //default 5
 *   Mode 3 : FASTEST , retry mode  : All port 25,2525,26,465,587 , SMTP_validateEmail->max_conn_time = 20; //default 30 , SMTP_validateEmail->max_read_time = 3 ; //default 5
 *   Mode 4 : SLOW , retry mode   : All port 25,2525,26,465,587 , SMTP_validateEmail->max_conn_time = 30; //default 30 , SMTP_validateEmail->max_read_time = 5 ; //default 5
 */

switch ($Performance_Mode) {
    case 1:
        $port_smtp = array(25);
        $SMTP_Validator->max_conn_time=4;
        $SMTP_Validator->max_read_time=2;
        $result = $DB_layer->select_next_non_processed($Number_Records,$Threads);// number of records as a parameter
        echo "Running in MODE 1 ::FASTEST \n number of record $Number_Records";
        break;
    case 2:
        $port_smtp = array(25);
        $SMTP_Validator->max_conn_time=20;
        $SMTP_Validator->max_read_time=3;
        $result = $DB_layer->select_next_non_processed($Number_Records,$Threads);// number of records as a parameter
        echo "Running in MODE 2 ::FAST \n number of record $Number_Records";
        break;
    case 3:
        $port_smtp = array(25,2525,26,465,587);
        $SMTP_Validator->max_conn_time=3;
        $SMTP_Validator->max_read_time=2;
        $result = $DB_layer->select_next_processed_retry($Number_Records,$Threads);// number of records as a parameter
        echo "Running in MODE 3 ::FASTEST , retry mode \n number of record $Number_Records";

        break;
    case 4:
        $port_smtp = array(25,2525,26,465,587);
        $SMTP_Validator->max_conn_time=30;
        $SMTP_Validator->max_read_time=5;
        $result = $DB_layer->select_next_processed_retry($Number_Records,$Threads);// number of records as a parameter
        echo "Running in MODE 4 ::SLOW , retry mode \n number of record $Number_Records";
        break;
}
if ($result)
{
    $count=0;
    while ($obj=$result->fetch_object()) {
        $email=$obj->email;
        $count++;
        foreach($port_smtp as $idp=>$port) {
            $results = $SMTP_Validator->validate(array($email), $sender, $port);
           // echo " times :::::$count <br>";
            if ($results!=2){
                break;
            }
        }
        $count_result[$results]++;

      //  echo "<br>$count - :$email::".$results;
        $DB_layer->update_processed($email=$obj->id,$results,$SMTP_Validator->public_msg,$Performance_Mode,$Threads);
    }
}
 echo " \n Processed :: $count\n";
   var_dump($count_result);
  echo "\n".@date('H:i:s', time())."\n Takes *****".(time() - $initial )." s ****\n\n\n\n";
$DB_layer->close();