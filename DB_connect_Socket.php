<?php


/**
 * Created by PhpStorm.
 * User: cpayret
 * Date: 12/1/2015
 * Time: 2:53 PM
 */
class DB_connect_Socket{

    private $db;
    private $par_con =  array('localhost', 'root', '4fl4cP3n6u1n', 'hcmk');
    public function __construct() {
        $this->db	= new mysqli($this->par_con[0] , $this->par_con[1], $this->par_con[2], $this->par_con[3]) ;
        if ($this->db->connect_error) {
            die('Connect Error: ' . $this->db->connect_error);
        }
    }

    public function select_next_non_processed($n) {

        $qry="SELECT id , email , socket_result , socket_msg , socket_date from consemail_100k_11 WHERE ISNULL(socket_result) AND id >=(SELECT IF((SELECT MAX(id)from consemail_100k_11 WHERE NOT ISNULL(socket_result)),(SELECT MAX(id)from consemail_100k_11 WHERE NOT ISNULL(socket_result)),0))LIMIT  $n";

        if (! $return_temp=$this->db->query($qry)) {
            die("Error message:". $this->db->error);
        }
        return $return_temp;


    }

    public function update_processed($id,$socket_result,$socket_msg,$socket_date="NOW()") {

    $qry="UPDATE consemail_100k_11 set socket_result='$socket_result' ,socket_msg='$socket_msg' , socket_date=$socket_date WHERE id=$id LIMIT 1 ";
        if (!$this->db->query($qry)) {
            die("Error message:". $this->db->error);        }

        return $this->db->affected_rows;
    }

}



$testbd=new DB_connect_Socket();
if ($result = $testbd->select_next_non_processed(50)) {
    while ($obj=$result->fetch_object()) {
        echo $obj->id . "    ";
        echo $obj->email . "<br>";
    }
}

//echo $testbd->update_processed(1,1,'test');





?>