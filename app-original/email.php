<?php
require_once __DIR__.'/server/classes/ApplicationConstants.php';
require_once __DIR__.'/phpmailer/class.phpmailer.php';
class Email{
	function sendEmail($to, $toName, $from, $fromName, $subject, $body, $plainTextBody, $attachments= false){
		$mail = new PHPMailer(); 
		$mail->IsSMTP(); 
		$mail->SMTPAuth = true; 
		$mail->Username = ApplicationConstants::SMTP_USERNAME; 
		$mail->Password = ApplicationConstants::SMTP_PASSWORD; 
		$webmaster_email = ApplicationConstants::WEBMASTER_EMAIL; 
		$email=$to; 
		$name=$toName; // Recipient's name
		$mail->From = $from;
		$mail->FromName = $fromName;
		$mail->AddAddress($email,$name);
		$mail->AddReplyTo($webmaster_email,$fromName);
		$mail->WordWrap = 50; // set word wrap
		if($attachments){
			$mail->AddAttachment("/var/tmp/file.tar.gz"); // attachment
			$mail->AddAttachment("/tmp/image.jpg", "new.jpg"); // attachment
		}
		$mail->IsHTML(true); // send as HTML
		$mail->Subject = $subject;
		$mail->Body = ($body);
		$mail->AltBody = $plainTextBody;
		if(!$mail->Send())
		{
			echo "Mailer Error: " . $mail->ErrorInfo;
		}
		else
		{
			//echo "Message has been sent";
		}
	}
}
?>