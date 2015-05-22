<?php
require_once 'email.php';
require_once __DIR__.'/server/classes/ApplicationConstants.php';
class MailContact{
	function __construct(){
		$data = json_decode(file_get_contents("php://input"));
		$this->sendContactMail($data);
	}
	public function sendContactMail($data){	
		$mail = new Email();
		$body = "<p>Name: ".$data->name."</p>".
				"<p>Email: ".$data->email."</p>".
				"<p>Message: ".$data->messagetext."</p>";
		$mail->sendEmail(ApplicationConstants::WEBMASTER_EMAIL, ApplicationConstants::WEBMASTER_NAME, $data->email, $data->name, "Contact Email", $body, $body);
	}
}

$contact = new MailContact();
?>