<?php
class UserRegistrationValidation extends UserValidation
{

    private $userrpw;
    private $fullname;

    private $errorCodes = array();

    const MAX_FULLNAME_LENGTH = 70;
    const MIN_FULLNAME_LENGTH = 12;

    function __construct($userData, $db) {
       $this->username = filter_var($userData['username'], FILTER_SANITIZE_STRING);
       $this->fullname = filter_var($userData['fname'], FILTER_SANITIZE_STRING);
       $this->userpw = filter_var($userData['password'], FILTER_SANITIZE_STRING);
       $this->userrpw = filter_var($userData['rpassword'], FILTER_SANITIZE_STRING);
       parent::__construct($db);
   }

	private function validateUserData(){
		$usernamel = strlen($this->username);
		$fullnamel = strlen($this->fullname);
		$userpwl = strlen($this->userpw);
		if($this->userrpw !== $this->userpw){
			$this->errorCodes[] = '101';
		}else if($userpwl < UserValidation::MIN_PASSWORD_LENGTH || $userpwl > UserValidation::MAX_PASSWORD_LENGTH){
			$this->errorCodes[] = '103';
		}
		if($usernamel < UserValidation::MIN_USERNAME_LENGTH || $usernamel > UserValidation::MAX_USERNAME_LENGTH){
			$this->errorCodes[] = '102';
		}
		if($fullnamel < UserRegistrationValidation::MIN_FULLNAME_LENGTH || $fullnamel > UserRegistrationValidation::MAX_FULLNAME_LENGTH){
			$this->errorCodes[] = '104';
		}
		if(empty($this->errorCodes))
			return true;
		else
			return false;
	}

	function registerUser(){
		$userV = $this->validateUserData();
		if($userV === true){
			$hashedpw = password_hash($this->userpw, PASSWORD_BCRYPT);
			$query = $this->db->prepare("insert into users (username, fullname, userpw, creationdate) values (:username , :fullname , :userpw , CURRENT_DATE)");
	        $query->bindParam(':username', $this->username, PDO::PARAM_STR);
	        $query->bindParam(':fullname', $this->fullname, PDO::PARAM_STR);
	        $query->bindParam(':userpw', $hashedpw, PDO::PARAM_STR);
	        $query->execute();
	        $error = $this->checkQueryError($query->errorCode());
	        if($error === '23505'){
	            $this->errorCodes[] = '201';
	        }else if($error !== false){
	            $this->errorCodes[] = '202';
	        }else{
	            $this->errorCodes[] = '200';
	        }
	        return $this->errorCodes;
    	}else{
    		return $this->errorCodes;
    	}
	}
}