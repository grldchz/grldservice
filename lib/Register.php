<?PHP 
/**
	This is a part of the grilledcheeseoftheday.com

	Copyright (C) 2022 grilledcheeseoftheday.com

    grilledcheeseoftheday.com is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    grilledcheeseoftheday.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see http://www.gnu.org/licenses/.
**/
require_once(dirname(__FILE__).'/Connect.php');
require_once(dirname(__FILE__).'/Mailer.php');
class Register extends Connect{
	private $firstname;
	private $lastname;
	private $email;
	private $captcha;
	public function __construct(){
		parent::__construct();	

		if (PHP_SAPI === 'cli') {
			$this->firstname = $argv[1];
			$this->lastname = $argv[2];
			$this->email = $argv[3];
		}
		else{
			if(isset($_POST["firstname"])&&$_POST["firstname"]!="") {
				$this->firstname = strtolower(trim($_POST["firstname"]));
			}
			if(isset($_POST["lastname"])&&$_POST["lastname"]!="") {
				$this->lastname = strtolower(trim($_POST["lastname"]));
			}
			if(isset($_POST["email"])&&$_POST["email"]!="") {
				$this->email = strtolower(trim($_POST["email"]));
			}
			if(isset($_POST['g-recaptcha-response'])&&
				$_POST["g-recaptcha-response"]!=""){
					$this->captcha=$_POST["g-recaptcha-response"];
			}
		}
	}
	public function register(){
		if (PHP_SAPI === 'cli') {
			$resp = (object) ['success' => true];
		}
		else{
			$resp = $this->verifyRecaptcha($this->captcha);
		}

		if ($resp->success == false){
			$gcotd_msg.= "".
				"The reCAPTCHA wasn't entered correctly. ".
				"Go back and try it again.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		} 
		else if($resp->success == true){
			$plainTxtPassword = getNewPassword();
			$memip = $_SERVER['REMOTE_ADDR'];
			$date = date("Y-m-d H:i:s");
			if($this->email == NULL 
				OR $this->firstname == NULL OR $this->lastname == NULL)
			{
				$gcotd_msg.= "Please complete the form.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else
			{
				if(!preg_match('/^[a-z\d_]{1,30}$/i', $this->firstname))
				{
					$gcotd_msg.="Your first name must be ".
						"between 1 and 30 characters cannot contain spaces or ".
						"special characters.";
					$this->setOutput(self::$FAIL, $gcotd_msg);
				}
				else
				{
					if(!preg_match('/^[a-z\d_]{1,30}$/i', $this->lastname))
					{
						$gcotd_msg.="Your last name must be ".
							"between 1 and 30 characters cannot contain spaces or ".
							"special characters.";
						$this->setOutput(self::$FAIL, $gcotd_msg);
					}
					else
					{
						if($this->firstname === $this->lastname)
						{
							$gcotd_msg.="Your first name cannot ".
								"equal your last name.";
							$this->setOutput(self::$FAIL, $gcotd_msg);
						}
						else
						{
							if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", 
								$this->email))
							{ 
								$gcotd_msg.="".$this->email." is ".
									"not a valid email address.";
								$this->setOutput(self::$FAIL, $gcotd_msg);
							}
							else
							{
								$check_user_stmt = $this->getDb()->prepare(
									"SELECT * FROM users WHERE email = :email");   
								$check_user_stmt->bindValue(':email',  $this->email, PDO::PARAM_STR);
								$check_user_stmt->execute();
								if($check_user_stmt->rowCount() > 0){
									$gcotd_msg.="Someone with ".
										"this email has already registered.";
									$this->setOutput(self::$FAIL, $gcotd_msg);											
								}
								else{ 
									$password = password_hash($plainTxtPassword, PASSWORD_DEFAULT);
									$this->registerUser(
										$this->firstname,
										$this->lastname,
										$this->email,
										$memip,
										$date,
										$password,
										$plainTxtPassword, $_SERVER['HTTP_USER_AGENT']);
								}
                            }
                        }
                    }
                }
            }
        }
		else{
			$gcotd_msg.= "Failed to verify captcha.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
    }
	public function registerUser(
						$firstname,
						$lastname,
						$email,
						$memip,
						$date,
						$password,
						$plainTxtPassword, $device){
		$create_stmt = $this->getDb()->prepare(
			"INSERT INTO users (".
				"first_name, ".
				"last_name, ".
				"password, ".
				"email, ".
				"ip, ".
				"create_date_time,device) VALUES (".
					":firstname, ".
					":lastname, ".
					":password, ".
					":email, ".
					":memip, ".
					":date,:device)"); 
		$create_stmt->bindValue(':firstname',  $firstname, PDO::PARAM_STR);
		$create_stmt->bindValue(':lastname',  $lastname, PDO::PARAM_STR);
		$create_stmt->bindValue(':password',  $password, PDO::PARAM_STR);
		$create_stmt->bindValue(':email',  $email, PDO::PARAM_STR);
		$create_stmt->bindValue(':memip',  $memip, PDO::PARAM_STR);
		$create_stmt->bindValue(':date',  $date, PDO::PARAM_STR);
		$create_stmt->bindValue(':device',  $device, PDO::PARAM_STR);
		$create_stmt->execute();
		$user_id = $this->getDb()->lastInsertId();
		if($create_stmt->rowCount() > 0)
		{
			$name = $firstname."_".$user_id;
			$stmt = $this->getDb()->prepare(
				"insert into skillet (user_id, friend_id) values (?, ?)");
			$stmt->execute(array($user_id, $user_id));
			$stmt = $this->getDb()->prepare(
				"insert into skillet (user_id, friend_id, hidden) values (?, ?, ?)");			
			$stmt->execute(array($this->get_admin_id(), $user_id, 1));
			$stmt = $this->getDb()->prepare(
				"insert into skillet (user_id, friend_id) values (?, ?)");
			$stmt->execute(array($user_id, $this->get_admin_id()));
			$stmt = $this->getDb()->prepare(
				"update users set name=:name WHERE id=:user_id");
			$stmt->bindValue(':name',  $name, PDO::PARAM_STR);
			$stmt->bindValue(':user_id',  intval($user_id), PDO::PARAM_INT);
			$stmt->execute();
			$select_stmt = $this->getDb()->query(
				"select * from users where id='".$user_id."'");
			$new_user_data = $select_stmt->fetch(PDO::FETCH_ASSOC);
			if($this->sendMail($new_user_data, $plainTxtPassword)){
				$success = true;
				$gcotd_msg="".
					"An email has been sent to $email with ".
					"registration details"; 
				$this->setOutput(self::$SUCCESS, $gcotd_msg);
			}
			else{
				$success = false;
				$gcotd_msg="".
					"Failed to send email to $email with ".
					"registration details. Please check your email and try again."; 
				$this->setOutput(self::$FAIL, $gcotd_msg);
				$select_stmt = $this->getDb()->query(
					"delete from users where id='".$user_id."'");
				$select_stmt = $this->getDb()->query(
					"delete from skillet where user_id='".$user_id."' or friend_id='".$user_id."'");
			}
		}
		else
		{
			$gcotd_msg="".
				"An error occured registering your account."; 
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}			
	}
	private function sendMail($user_data, $plainTxtPassword){
		$to       = $user_data["email"];
		$subject  = $this->get_title()." Registration: ".$user_data["name"];
		$message  = "Thank You for registering at ".$this->get_title().".\r\n";
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