<?php
/**
 * Created by PhpStorm.
 * User: cpayret
 * Date: 12/1/2015
 * Time: 2:53 PM
 */

/** DB layer extend mysqli */
class DB_connect_I extends mysqli {
    private $par_con =  array('localhost', 'root', '4fl4cP3n6u1n', 'mail_socket_app');
    // private $db_name = array ("consemail_100k_11","consemail_100k_19","consemail_100k_21","consemail_100k_25","consemail_100k_3");
    // private $db_name = array ("consemail_100k_4","consemail_100k_10","consemail_100k_15","consemail_100k_18","consemail_100k_30");
    // private $db_name = array ("consemail_1m_18","consemail_1m_19","consemail_1m_27","consemail_1m_28");
    // private $db_name = array ("registered_reps_1","registered_reps_2","registered_reps_3","registered_reps_4");

  //  private $db_name = array ("RIADatabaseSelectedContactEmails_1","RIADatabaseSelectedContactEmails_2");
  //  private $db_name = array ("RIADatabaseSelectedContactEmails_1","RIADatabaseSelectedContactEmails_2");

    private $db_name = array ("temp_clean_socket_1","temp_clean_socket_2");



    public function __construct() {
        parent::__construct($this->par_con[0] , $this->par_con[1], $this->par_con[2], $this->par_con[3]);
        if ($this->connect_error) {
            die('Connect Error: ' . $this->connect_error);
        }
    }
    public function select_next_non_processed($n,$threads=0) {
        $qry="SELECT id , email , socket_result , socket_msg , socket_date from ".$this->db_name[$threads]." WHERE ISNULL(socket_result) AND email is NOT NULL AND id >=(SELECT IF((SELECT MAX(id) FROM ".$this->db_name[$threads]." WHERE NOT ISNULL(socket_result)),(SELECT MAX(id)FROM ".$this->db_name[$threads]." WHERE NOT ISNULL(socket_result)),0))LIMIT ".intval ($n);

  echo  $qry;

        if (! $return_temp=$this->query($qry)) {
            $this->alter_tables_processed($threads);
          //  die("Error message:". $this->error);
        }
        return $return_temp;
    }
    public function select_next_processed_retry($n,$threads=0) {
        $qry="SELECT id , email , socket_result , socket_msg , socket_date FROM ".$this->db_name[$threads]." WHERE socket_result = 2 AND socket_performance_mode < 3 AND email is NOT NULL ORDER by id LIMIT ".intval($n);
        if (! $return_temp=$this->query($qry)) {
            die("Error message:". $this->error);
        }
        return $return_temp;
    }
    public function update_processed($id,$socket_result,$socket_msg,$Performance_Mode,$threads=0, $socket_date="NOW()") {
    $qry="UPDATE ".$this->db_name[$threads]." set socket_result='$socket_result' ,socket_msg='".$this->real_escape_string($socket_msg)."' ,  socket_date=$socket_date ,  socket_performance_mode=$Performance_Mode   WHERE id=$id LIMIT 1 ";
        if (!$this->query($qry)) {
            die("Error message:". $this->error);        }
        return $this->affected_rows;
    }
    /**
     * @param $threads
     */
    private function alter_tables_processed($threads) {

/*
        $qry="ALTER TABLE ".$this->db_name[$threads]."
    ADD COLUMN id int(11) NOT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (id),
    ADD COLUMN socket_result  int(11) NULL DEFAULT NULL COMMENT '1 valid , 0 Not valid , 2 unknown , NULL no processed',
    ADD COLUMN socket_msg  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER socket_result,
    ADD COLUMN socket_date  datetime NULL DEFAULT NULL AFTER socket_msg,
    ADD COLUMN socket_performance_mode  int(11) NULL DEFAULT 1 AFTER socket_date;";
      */

        $qry="ALTER TABLE ".$this->db_name[$threads]."
    ADD COLUMN id int(11) NOT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (id),
    ADD COLUMN socket_result  int(11) NULL DEFAULT NULL COMMENT '1 valid , 0 Not valid , 2 unknown , NULL no processed',
    ADD COLUMN socket_msg  varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL AFTER socket_result,
    ADD COLUMN socket_date  datetime NULL DEFAULT NULL AFTER socket_msg,
    ADD COLUMN socket_performance_mode  int(11) NULL DEFAULT 1 AFTER socket_date;";

        $msgs="************** ALTER table success , lets wait next run **************** ";
        if (!$this->query($qry)) {
            $msgs="Error message:". $this->error."<BR>".$qry;
        }
        die($msgs);

    }

}





