<?php
/**
    GRLDCHZ - a PHP REST-like backing for a social network
	/grldservice/lib/Register.php is part of GRLDCHZ
	
    Copyright (C) 2021 grilledcheeseoftheday.com

    GRLDCHZ is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    GRLDCHZ is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require(dirname(__FILE__).'/../../vendor/PHPMailer/src/Exception.php');
require(dirname(__FILE__).'/../../vendor/PHPMailer/src/PHPMailer.php');
require(dirname(__FILE__).'/../../vendor/PHPMailer/src/SMTP.php');
require_once(dirname(__FILE__).'/Connect.php');
class Mailer extends Connect{
	public function __construct(){
		parent::__construct();	
	}
	public function sendmail($email, $subject, $body){
		// Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer(true);

		try {
			//Server settings
			$mail->SMTPDebug = 0;                                       // Enable verbose debug output
			$mail->isSMTP();                                            // Set mailer to use SMTP
			$mail->Host       = $this->get_smtp_host();  // Specify main and backup SMTP servers
			$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
			$mail->Username   = $this->get_admin_email();               // SMTP username
			$mail->Password   = $this->get_admin_email_password();      // SMTP password
			$mail->SMTPSecure = 'ssl';                   // Enable TLS encryption, `ssl` also accepted
			$mail->Port       = $this->get_smtp_port();                 // TCP port to connect to

			//Recipients
			$mail->setFrom($this->get_admin_email(), $this->get_title());
			$mail->addAddress($email);     // Add a recipient
			//$mail->addAddress($this->get_admin_email());               // Name is optional
			//$mail->addReplyTo($this->get_admin_email(), 'Information');
			$mail->addCC($this->get_admin_email());
			//$mail->addBCC('bcc@example.com');

			// Attachments
			//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $body;
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			if($mail->send()){
				return true;
			}
			else{
				return false;
			}
		} catch (Exception $e) {
			error_log("Email sending failed. \r\n".$email."\r\n".$subject."\r\n".$body);
			return false;
		}
	}
}
//test
if(PHP_SAPI == 'cli') {
	echo "Mailer";
	$mailer = new Mailer();
	if($mailer->sendmail($argv[1], $argv[2], $argv[3])){
		echo "MAIL SUCCESS";
	}
	else{
		echo "MAIL FAILURE";
	}
}

?>