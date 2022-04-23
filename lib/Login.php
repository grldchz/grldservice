<?PHP
/**
    GRLDCHZ - a PHP REST-like backing for a social network
	/grldservice/lib/Login.php is part of GRLDCHZ
	
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
require_once(dirname(__FILE__).'/Register.php');
class Login extends Connect{
	private $postUsername;
	private $postPassword;
	private $fbid;
	private $fbfirstname;
	private $fblastname;
	private $captcha;
	public function __construct(){
		parent::__construct();	
		if(isset($_POST["username"])&&$_POST["username"]!="") {
			$this->postUsername = strtolower(trim($_POST["username"]));
		}
		if(isset($_POST["password"])&&$_POST["password"]!="") {
			$this->postPassword = $_POST["password"];
		}
		if(isset($_POST["fbid"])&&$_POST["fbid"]!="") {
			$this->fbid = $_POST["fbid"];
		}
		if(isset($_POST["fbfirstname"])&&$_POST["fbfirstname"]!="") {
			$this->fbfirstname = $_POST["fbfirstname"];
		}
		if(isset($_POST["fblastname"])&&$_POST["fblastname"]!="") {
			$this->fblastname = $_POST["fblastname"];
		}
		if(isset($_POST['g-recaptcha-response'])&&
			$_POST["g-recaptcha-response"]!=""){
				$this->captcha=$_POST["g-recaptcha-response"];
		}
	}
	public function login(){
		if($this->postUsername == NULL OR $this->postPassword == NULL)
		{
			$gcotd_msg = "Please complete all the fields.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
		$captchaVerified = true;
		/*
		$captchaVerified = false;
		if($this->fbid == null AND $this->fbfirstname == null AND $this->fblastname == null){
			$resp = verifyRecaptcha($this->captcha);
			if ($resp->success == false)
			{
				$gcotd_msg = "".
					"The reCAPTCHA wasn't entered correctly. ".
					"Go back and try it again.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else{
				$captchaVerified = true;
			}
		}
		*/
		if($captchaVerified == true AND $this->postUsername != NULL AND $this->postPassword != NULL){
			$check_user_stmt = $this->getDb()->prepare("SELECT * FROM users 
				WHERE (name = :username OR email = :username2)");
			$check_user_stmt->bindValue(':username',  $this->postUsername, PDO::PARAM_STR);
			$check_user_stmt->bindValue(':username2',  $this->postUsername, PDO::PARAM_STR);
			$check_user_stmt->execute();
					
			if($check_user_stmt->rowCount() == 0){
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else
			{
				$user_data = $check_user_stmt->fetch(PDO::FETCH_ASSOC);
			}
				if($user_data['password'] == getPassword($this->postPassword)){
					//old insecure password
					$user_data['password'] = password_hash($this->postPassword, PASSWORD_DEFAULT);
					$update_password_stmt = $this->getDb()->prepare("update users  
						set password = :hashedPassword WHERE id = :user_id");
					$update_password_stmt->bindValue(':hashedPassword',  $user_data['password'], PDO::PARAM_STR);
					$update_password_stmt->bindValue(':user_id',  $user_data['id'], PDO::PARAM_STR);
					$update_password_stmt->execute();
				}
				if(password_verify($this->postPassword, $user_data['password']))
				{
					if(getPassword($user_data['email']) == $password)
					{
						$gcotd_msg.="You are being 
							redirected to change your password, 
							please wait a few moments.";
							$this->setOutput("CHANGE_PASSWORD", $gcotd_msg);
					}
					else
					{
						$arr_cookie_options = array (
							'expires' => time() + (60 * 60 * 24 * 184),
							'path' => '/',
							'domain' => $this->get_domain(),
							'secure' => $this->get_secure(),
							'httponly' => true,
							'samesite' => 'Strict'
						);
						// set cookie that expires in 6 months
						if (explode('.', PHP_VERSION)[0] < 7){
							setcookie($this->get_cookie_name(), $user_data['id'].".".$user_data['password'], time() +
								(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
						}
						else{
							setcookie($this->get_cookie_name(), $user_data['id'].".".$user_data['password'], $arr_cookie_options);
							//file_put_contents("/var/www/html/grldservice/debug.log", "Login.php: setcookie successful\n", FILE_APPEND);
						}
						$success = true;
						$gcotd_msg.="You are being logged in, 
							please wait a few moments.";
						$this->setOutput(self::$SUCCESS, $user_data);
					}
				}
				else{
					$gcotd_msg.="Your login credentials are incorrect, 
						please try again.";
					$this->setOutput(self::$FAIL, $gcotd_msg);
				}
		}
	}
	public function logout(){
		$arr_cookie_options = array (
			'expires' => time() - 3600,
			'path' => '/',
			'domain' => $this->get_domain(),
			'secure' => $this->get_secure(),
			'httponly' => true,
			'samesite' => 'Strict'
		);
		if (explode('.', PHP_VERSION)[0] < 7){
			setcookie($this->get_cookie_name(), "", time() +
				(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
			setcookie($this->get_cookie_name()."terms", false, time() +
				(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
			setcookie($this->get_cookie_name()."guest", false, time() +
				(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
		}
		else{
			setcookie($this->get_cookie_name(), "", $arr_cookie_options);
			setcookie($this->get_cookie_name()."terms", false, $arr_cookie_options);
			setcookie($this->get_cookie_name()."guest", false, $arr_cookie_options);
		}
		unset($_COOKIE[$this->get_cookie_name()]);
		unset($_COOKIE[$this->get_cookie_name()."terms"]);
		unset($_COOKIE[$this->get_cookie_name()."guest"]);
		$gcotd_msg="Logged out";
		$this->setOutput(self::$SUCCESS, $gcotd_msg);

	}
}
?>