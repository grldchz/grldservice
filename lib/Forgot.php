<?PHP 
/**
    GRLDCHZ - a PHP REST-like backing for a social network
	/grldservice/lib/Forgot.php is part of GRLDCHZ
	
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
require_once(dirname(__FILE__).'/Connect.php');
require_once(dirname(__FILE__).'/Mailer.php');
class Forgot extends Connect{
	private $postedusername;
	private $postedemail;
	private $captcha;
	public function __construct(){
		parent::__construct();	
		if(isset($_POST["username"])){
			$this->postedusername = strtolower(trim($_POST["username"]));
		}
		if(isset($_POST["email"])){
			$this->postedemail = strtolower(trim($_POST["email"]));
		}
		if(isset($_POST['g-recaptcha-response'])&&
			$_POST["g-recaptcha-response"]!=""){
				$this->captcha=$_POST["g-recaptcha-response"];
		}
	}
	public function forgot(){
		$resp = $this->verifyRecaptcha($this->captcha);

		if ($resp->success == false){
			$gcotd_msg = "".
				"The reCAPTCHA wasn't entered correctly. ".
				"Go back and try it again.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		} 
		else if($resp->success == true){
			if($this->postedusername == NULL AND $this->postedemail == NULL){
				$gcotd_msg = "Please complete the form.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else{
				if($this->postedusername != null){
					$check_user_stmt = $this->getDb()->prepare(
						"SELECT * FROM users WHERE name = :username");   
					$check_user_stmt->bindValue(':username',  $this->postedusername, PDO::PARAM_STR);
					$check_user_stmt->execute();
					if($check_user_stmt->rowCount() > 0){
						$get_user_data = $check_user_stmt->fetch(PDO::FETCH_ASSOC);
						$user_id = $get_user_data['id'];
						$resetPassword = $this->resetPassword($user_id);
						if($resetPassword != false){
							if($this->sendMail($get_user_data, $resetPassword)){
								$gcotd_msg = "An email has been sent 
									to the email address registered for that username.";
								$this->setOutput(self::$SUCCESS, $gcotd_msg);
							}
							else{
								$gcotd_msg="".
									"Failed to send email to ".
									"to the email address registered for that username.";
								$this->setOutput(self::$FAIL, $gcotd_msg);
							}
						}
					}
					else{
						$gcotd_msg = "This user name does not exist.";
						$this->setOutput(self::$FAIL, $gcotd_msg);
					}
				}
				else if($this->postedemail != null){
					if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $this->postedemail)){ 
						$gcotd_msg = "Your email address is not valid.";
						$this->setOutput(self::$FAIL, $gcotd_msg);
					}
					else{
						$check_user_stmt = $this->getDb()->prepare(
							"SELECT * FROM users WHERE email=:email");   
						$check_user_stmt->bindValue(':email',  $this->postedemail, PDO::PARAM_STR);
						$check_user_stmt->execute();
						if($check_user_stmt->rowCount() > 0){
							$get_user_data = $check_user_stmt->fetch(PDO::FETCH_ASSOC);
							$user_id = $get_user_data['id'];
							$resetPassword = $this->resetPassword($user_id);
							if($resetPassword != false){
								if($this->sendMail($get_user_data, $resetPassword)){
									$gcotd_msg = "An email has been sent 
										to the email address registered for that username.";
									$this->setOutput(self::$SUCCESS, $gcotd_msg);
								}
								else{
									$gcotd_msg="".
										"Failed to send email to ".
										"the email address registered for that username.";
									$this->setOutput(self::$FAIL, $gcotd_msg);
								}
							}
							else{
								$gcotd_msg = "Your password could not be reset.";
								$this->setOutput(self::$FAIL, $gcotd_msg);
							}
						}
						else{
							$gcotd_msg = "This email does not exist.";
							$this->setOutput(self::$FAIL, $gcotd_msg);
						}
					}
				}
			}
		}
		else{
			$gcotd_msg = "Failed to verify captcha.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
	}
	private function resetPassword($user_id){
		$plainTxtPassword = getNewPassword();
		$password = getPassword($plainTxtPassword);
		$stmt = $this->getDb()->prepare(
			"update users set password=:password WHERE id=:user_id");
		$stmt->bindValue(':password',  $password, PDO::PARAM_STR);
		$stmt->bindValue(':user_id',  intval($user_id), PDO::PARAM_INT);
		$stmt->execute();

		if ($stmt->rowCount() > 0){
			return $plainTxtPassword;
		}
		else{
			return false;
		}		
	}
	private function sendMail($user_data, $plainTxtPassword){
		$to       = $user_data["email"];
		$subject  = $this->get_title()." Password Reset: ".$user_data["name"];
		$message  = "Your password has been reset..\r\n";
		$message  .= "The following are your credentials: \r\n";
		$message  .= "User Name: ".$user_data["name"]."\r\n"; 
		$message  .= "Pass Word: ".$plainTxtPassword;
		$mailer = new Mailer();
		if($mailer->sendmail($to, $subject, $message)){
			return true;
		}
		else{
			error_log("Email sending failed. \r\n".$to."\r\n".$subject."\r\n".$message);
			return false;
		}
	}
}
?>