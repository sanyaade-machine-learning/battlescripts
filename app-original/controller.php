<?php
require_once 'LoginBroker.php';
require_once __DIR__.'/server/classes/ApplicationConstants.php';

class Controller{
	private $config;
	private $hybridauth;
	private $broker;
	private $provider;
	private $admin = false;
	
	public function __construct(){
		$this->broker = new LoginBroker();
		$userdata = array();
		foreach ($_REQUEST as $key=>$value){
			$userdata[$key] = $value;
		}
		if($this->validateToken($userdata)){
			$user = $this->authenticateUser(empty($userdata['first'])?$userdata['name']:"", $userdata['last'], $userdata['provider'], $userdata['userAuthId'], $userdata['email'] );
			$this->printAJAXResponse(false, array('message'=>'Authentication Success','code'=>$user['type']));
		}
		else{
			$this->printAJAXResponse(true, array('message'=>'Authentication Error: Token not valid.','code'=>'401'));
		}
	}
	
	public function authenticateUser($first, $last, $provider, $userAuthId, $email ){
		$user = $this->broker->checkUser($first, $last, $provider, $userAuthId, $email);
		//Set the user to the session 
		if (session_id() == '') {
		    session_start();
		}
		$_SESSION['user'] = $user;
		return $user;
	}
	public function validateToken($userdata){
		$authenticated = false;
		$query = http_build_query(array('access_token'=>$userdata['accessToken']));
		switch($userdata['provider']){
			case 'google':
				$url = ApplicationConstants::GOOGLE_VALIDATE_TOKEN_URL.$query;
				$result = json_decode(file_get_contents($url));
				if($result->user_id == $userdata['userAuthId'])
					$authenticated = true;
				break;
			case 'github':
				$result = $this->fakeBrowserOutput($userdata['accessToken']);
				if(is_object($result) && $result->id == $userdata['userAuthId'])
					$authenticated = true;
				break;
			case 'facebook':
				$url = ApplicationConstants::FACEBOOK_VALIDATE_TOKEN_URL.$query;
				$result = json_decode(file_get_contents($url));
				if($result->id == $userdata['userAuthId'])
					$authenticated = true;
				break;
		}
		return $authenticated;
	}
	public function printAJAXResponse($error,$data){
		if (!$error)
		{
			header('Content-Type: application/json');
			print json_encode($data['message']);
		}
		else
		{
			header('HTTP/1.1 500 Internal Server Error');
			header('Content-Type: application/json; charset=UTF-8');
			die(json_encode($data['code'].' - '.$data['message']));
		}
	}
	public function fakeBrowserOutput($access_token){
		if (session_id() == '') {
			session_start();
		}
		$url= ApplicationConstants::GITHUB_VALIDATE_URL . 'user';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$headers = array();
		$headers[] = 'Accept: application/json';
		$headers[] = 'Authorization: Bearer ' . $access_token;
		$headers[] = 'User-Agent: Battle Scripts Local';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		$user = json_decode($response);
		return $user;
	}
}
$controller = new Controller();
?>