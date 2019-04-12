<?php
/**
 * Created by PhpStorm.
 * User: cpayret
 * Date: 11/27/2015
 * Time: 5:08 PM
 */

// include SMTP Email Validation Class
require_once('smtp_validateEmail.php');

// the email to validate
$emails = array('user@example.com', 'user2@example.com');
$emails = array('fgnfgnfgn@jwkorth.com','sdvsd@clarian.com','gerencia@jbge.com.mx','claudio.payret@hotmail.com','habanadj@yahoo.com','claudiopayret@gmail.com');
// an optional sender
$sender = 'user@yourdomain.com';
// instantiate the class
$SMTP_Validator = new SMTP_validateEmail();
// turn on debugging if you want to view the SMTP transaction
$SMTP_Validator->debug = true;
// do the validation
$results = $SMTP_Validator->validate($emails, $sender);

// view results
foreach($results as $email=>$result) {
    // send email?
    if ($result) {
        //mail($email, 'Confirm Email', 'Please reply to this email to confirm', 'From:'.$sender."\r\n"); // send email
    } else {
        echo 'The email address '. $email.' is not valid';
    }
}
