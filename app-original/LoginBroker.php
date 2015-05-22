<?php

require_once $_SERVER["DOCUMENT_ROOT"].'/server/UtilityFunctions.php';
		
 class LoginBroker{
 	private $db;
 	public function __construct(){
		$this->db=DataBroker::singleton();
 	}
	
 	public function checkUser($first, $last, $providerId, $userAuthId, $email){
 		// 1= User does not exist (New user) 2= User exists but does not have admin access 3=Admin user
 		$user = array();
 		$user['name'] = $first;
 		$user['type'] = 1;
 		
 		//Check if the user already exists
 		$query = "SELECT * FROM `USER` WHERE AUTH_PROVIDER = '$providerId' AND AUTH_ID = '$userAuthId'";
 		$stmt=$this->db->prepare($query);
		$stmt->execute();
		$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
 		$row_cnt = $stmt->rowCount();
 		
 		if($row_cnt>0){ //Existing user
 			foreach ($data as $row)  {
	 			if($row['SUPER_USER'] == 'Y')
	 				$user['type'] = 3;
	 			else
	 				$user['type'] = 2;
	 			$user['id'] = $row['USER_ID'];
	 		}
 		}
 		else{ 
 			//Try to insert the new user in the database
 			$query = "INSERT INTO `USER` (FIRST_NAME, LAST_NAME, AUTH_PROVIDER, AUTH_ID, EMAIL_ADDRESS) VALUES ('$first', '$last', '$providerId', '$userAuthId', '$email')";
 			$stmt=$this->db->prepare($query);
			$stmt->execute();
 			$user['id']=$this->db->lastInsertId();
 		}
 		return $user;
 	}
 }
 ?>