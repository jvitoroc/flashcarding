<?php
class UserLoginValidation extends UserValidation
{

	private $session;

	function __construct($userData, $db, $session) {
       $this->username = filter_var($userData['username'], FILTER_SANITIZE_STRING);
       $this->userpw = filter_var($userData['password'], FILTER_SANITIZE_STRING);
       $this->session = $session;
       parent::__construct($db);
   }

	private function validateUserData(){
		$usernamel = strlen($this->username);
		$userpwl = strlen($this->userpw);
		if($usernamel < UserLoginValidation::MIN_USERNAME_LENGTH || $usernamel > UserLoginValidation::MAX_USERNAME_LENGTH){
			return '211';
		}else if($userpwl < UserLoginValidation::MIN_PASSWORD_LENGTH || $userpwl > UserLoginValidation::MAX_PASSWORD_LENGTH){
			return '211';
		}
		return true;
	}

	private function createSession($userid, $session){
    	$this->session->set("LOGINSERIAL", $userid);
	}

	function loginUser(){
		$userV = $this->validateUserData();
		if($userV === true){
			$query = $this->db->prepare("select userpw, userid from users where username = :username");
		    $query->bindParam(':username', $this->username, PDO::PARAM_STR);
		    $query->execute();
		    $error = $this->checkQueryError($query->errorCode());
		    if($error === false){
		    	$row = $query->fetch();
		    	if($row !== false){
		    		if(isset($row['userpw']) && password_verify($this->userpw, $row['userpw'])){
		    			$this->createSession($row['userid'], $this->session);
		    			return '210';
		    		}
		    	}else{
		    		return '211';
		    	}
		    }else{
		    	return '212';
		    }
		}else{
			return $userV;
		}
	}
}