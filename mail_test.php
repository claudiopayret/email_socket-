<?php
/**
 * Created by PhpStorm.
 * User: cpayret
 * Date: 11/30/2015
 * Time: 11:46 AM
 */


$email="user@yourdomain.com";

$headers = 'From: '.$email;


// echo mail('cpayret@jwkorth.com', 'Test email using PHP', 'This is a test email message'.time(), $headers, 'cpayret@jwkorth.com');


 
$to = $email;
$subject = 'Test email using PHP';
$message = 'This is a test email message '.time();
$headers = 'From: '.$email . "\r\n" . 'Reply-To: '.$email . "\r\n" . 'X-Mailer: PHP/' . phpversion();
echo mail($to, $subject, $message, $headers);

