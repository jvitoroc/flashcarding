<?php
class SessionAuthentication extends DatabaseConnection
{

	private $serial;

	function __construct($db, $serial){
		$this->serial = $serial;
		parent::__construct($db);
	}

	function authenticateSerial(){
        $query = $this->db->prepare("select userid from users where userid = :userid");
        $query->bindParam(':userid', $this->serial, PDO::PARAM_STR);
        $query->execute();
        $error = $this->checkQueryError($query->errorCode());
        if($error === false){
        	$row = $query->fetch();
	        if($row === false){
	            return false;
	        }else{
	            return true;
	        }
	    }
	    return false;
	}
}