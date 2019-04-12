<?php

/**
 * Created by PhpStorm.
 * User: cpayret
 * Date: 11/27/2015
 * Time: 4:52 PM
 */

/**
 *
 * 1 valid , 0 Not valid , 2 unknown , NULL no processed
 *
 *
 * avg run time
 *  1 valid :: 1s
 *  0 valid :: 0.5s
 *  2 unknown:: 5s
 *
 * */
class SMTP_validateEmail {
    /**
     * @var resource Declarations
     */
    var $sock=0;#
    var $user='';
    var $domain='';
    var $domains='';
    /**
     * Maximum Connection Time to wait for connection establishment per MTA
     */
    var $max_conn_time = 20; //default 30
    /**
     * Maximum time to read from socket before giving up
     */
    var $max_read_time = 3; //default 5
    var $from_user = 'user';
    var $from_domain = 'jwkorth.com';

    /**
     * Nameservers to use when make DNS query for MX entries
     * @var Array $nameservers
     */
    var $nameservers = array(
        '192.168.0.1'
    );

    var $debug = false;
    public $public_msg = "";
    public $public_cont = 0;

    /**
     * Initializes the Class
     * @return SMTP_validateEmail Instance
     * @param $email Array[optional] List of Emails to Validate
     * @param $sender String[optional] Email of validator
     */
    function SMTP_validateEmail($emails = false, $sender = false) {
        if ($emails) {
            $this->setEmails($emails);
        }
        if ($sender) {
            $this->setSenderEmail($sender);
        }
    }
    function _parseEmail($email) {
        $parts = explode('@', $email);
        $domain = array_pop($parts);
        $user= implode('@', $parts);
        return array($user, $domain);
    }
    /**
     * Set the Emails to validate
     * @param $emails Array List of Emails
     */
    function setEmails($emails) {
        foreach($emails as $email) {
            list($user, $domain) = $this->_parseEmail($email);
            $this->domains  = array();
            $this->domains[$domain][]=$user;
        }
    }
    /**
     * Set the Email of the sender/validator
     * @param $email String
     */
    function setSenderEmail($email) {
        $parts = $this->_parseEmail($email);
        $this->from_user = $parts[0];
        $this->from_domain = $parts[1];
    }
    /**
     * Validate Email Addresses
     * @param String $emails Emails to validate (recipient emails)
     * @param String $sender Sender's Email
     * @return Array Associative List of Emails and their validation results

     */
    function validate($emails = false, $sender = false , $port_var=25) {
        $this->public_msg = "";
        $results = array();
        if ($emails) {
            $this->setEmails($emails);
        }
        if ($sender) {
            $this->setSenderEmail($sender);
        }
        // query the MTAs on each Domain
        // return 1 valid , 0 Not valid , 2 unknown , NULL no processed
        foreach($this->domains as $domain=>$users) {
            $mxs = array();
            // current domain being queried
            $this->domain = $domain;
            // retrieve SMTP Server via MX query on domain
            list($hosts, $mxweights) = $this->queryMX($domain);
            for($n=0; $n < count($hosts); $n++){
                $mxs[$hosts[$n]] = $mxweights[$n];
            }
            asort($mxs);
            // last fallback is the original domain
            $mxs[$this->domain] = 0;
             $this->debug(print_r($mxs, 1));

            $timeout = $this->max_conn_time;
            // try each host
            while(list($host) = each($mxs)) {
                // connect to SMTP server
                $this->debug(" try $host:$port_var");
                if (@$this->sock = fsockopen($host, $port_var, $errno, $errstr, (float) $timeout)) {
                    stream_set_timeout($this->sock, $this->max_read_time);
                    break;
                }
            }
            // did we get a TCP socket
            if ($this->sock) {
                $reply = fread($this->sock, 2082);
                $this->debug("<<<\n $reply");

                preg_match('/^([0-9]{3}) /ims', $reply, $matches);
                $code = isset($matches[1]) ? $matches[1] : '';

                if($code != '220') {
                    // MTA gave an error...
                    foreach($users as $user) {
                        $results[$user.'@'.$domain] = 2;
                        $this->debug("No respond");
                    }
                    continue;
                }
                // say helo
                $this->send("HELO ".$this->from_domain);
                // tell of sender
                $this->send("MAIL FROM: <".$this->from_user.'@'.$this->from_domain.">");

                // ask for each recepient on this domain
                foreach($users as $user) {

                    // ask of recepient
                    $reply = $this->send("RCPT TO: <".$user.'@'.$domain.">");

                    // get code and msg from response
                    preg_match('/^([0-9]{3}) /ims', $reply, $matches);
                    $code = isset($matches[1]) ? $matches[1] : '';

                    if ($code == '250') {
                        // you received 250 so the email address was accepted
                        $results[$user.'@'.$domain] = 1;
                    } elseif ($code == '451' || $code == '452') {
                        // you received 451 so the email address was greylisted (or some temporary error occured on the MTA) - so assume is ok
                        $results[$user.'@'.$domain] = 1;
                    } else {
                        $results[$user.'@'.$domain] = 0;
                    }
                }
                // reset before quit
                $this->send("RSET");

                // quit
                $this->send("quit");
                // close socket
                fclose($this->sock);

            } else {
                $this->debug('Error: Could not connect to a valid mail server for this email address: ' . $user.'@'.$domain);

                $results[$user.'@'.$domain] = 2;
            }
        }

        return $results[$user.'@'.$domain];
        //return $results;
    }
    function send($msg) {
        fwrite($this->sock, $msg."\r\n");
        $reply = fread($this->sock, 2082);
        $this->debug(">>>\n$msg\n");
        $this->debug("<<<\n$reply");
        return $reply;
    }
    /**
     * Query DNS server for MX entriesg
     * @return
     */
    function queryMX($domain) {
        $hosts = array();
        $mxweights = array();
            getmxrr($domain, $hosts, $mxweights);
        return array($hosts, $mxweights);
    }
    /**
     * Simple function to replicate PHP 5 behaviour. http://php.net/microtime
     */
    function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    function debug($str) {
        if ($this->debug) {
            echo '<pre>'.htmlentities($str).'</pre>';
        }
        $this->public_msg=$this->public_msg.$str;
    }

}

