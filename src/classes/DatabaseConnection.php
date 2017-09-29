<?php
abstract class DatabaseConnection
{

	protected $db;

	function __construct($db) {
       $this->db = $db;
   	}

   	protected function checkQueryError($code){
	    if($code === '00000'){
	    	return false;
	    }else{
	    	return $code;
	    }
	}
}