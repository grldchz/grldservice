<?php
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
require_once(dirname(__FILE__).'/password.php');
require_once(dirname(__FILE__).'/Connect.php');
require_once(dirname(__FILE__).'/Skillet.php');

class Auth extends Connect{
	public $user_data;
	private $cookieUserName;
	private $cookiePassword;
	public function __construct(){
		parent::__construct();
		if (PHP_SAPI === 'cli') {
			$this->cookie_gcotd = $argv[1];
		}
		else {
			if(isset($_COOKIE[$this->get_cookie_name()]) && $_COOKIE[$this->get_cookie_name()] != null){
				$this->cookie_gcotd = $_COOKIE[$this->get_cookie_name()];
			}
		}
	}
	public function authenticate(){
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			header('HTTP/1.0 200 Success');
			header('Allow: OPTIONS, GET, POST');
			$this->setOutput(self::$SUCCESS, "");
			exit;
		}
		/*
		if(!isset($this->cookie_gcotd)){
			$gcotd_msg="Not logged in.";
			header('HTTP/1.0 403 Forbidden');
			$this->setOutput(self::$FAIL, $gcotd_msg);
			throw new Exception();
		}
		else{
			*/
			$this->lookupUserInfo();
		//}
	}
	private function lookupUserInfo(){
		//foreach (getallheaders() as $name => $value) {
		//	file_put_contents($this->get_path()."/debug.log", "Auth: lookupUserInfo: header: $name: $value\n", FILE_APPEND);
		//}
		if(isset($this->cookie_gcotd)){
			$cookie_id = substr($this->cookie_gcotd, 0, strpos($this->cookie_gcotd, "."));
			$cookie_password = substr($this->cookie_gcotd, strlen($cookie_id)+1);
			$users_sql = "SELECT * FROM users WHERE 
				id = :user_id 
				and password = :password";
			$results = $this->getDb()->prepare($users_sql);
			$results->bindValue(':user_id',  $cookie_id, PDO::PARAM_STR);
			$results->bindValue(':password', $cookie_password, PDO::PARAM_STR);
		}
		else{
			$users_sql = "SELECT * FROM users WHERE 
				id = \"2\"";
			$results = $this->getDb()->prepare($users_sql);
		}
		
		$results->execute();
		$total = $results->rowCount();		
		if($total == 0){
			$gcotd_msg="Authentication failed.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
			throw new Exception($gcotd_msg);
		}
		else{
			$this->user_data = $results->fetch(PDO::FETCH_ASSOC);
		}
		$terms_accepted = $this->user_data['terms_accepted'];

		if($terms_accepted == 1 && $this->user_data['name'] == "guest" && !isset($this->cookie_gcotd))
		{
			if(isset($_POST["cookies_accepted"])){
				$this->acceptCookies();
			}
			$this->user_data['cookie_policy'] = true;
			$this->setOutput("COOKIES", $this->user_data);			
		}
		else if($terms_accepted == 1 && $this->user_data['name'] != "guest")
		{
			//file_put_contents($this->get_path()."/debug.log", "\nAuth Handling terms", FILE_APPEND);
			if(isset($_POST["terms_accepted"])){
				$this->acceptTerms();
			}
			else{

				$arr_cookie_options = array (
					'expires' => time() + (60 * 60 * 24 * 184),
					'path' => '/',
					'domain' => $this->get_domain(),
					'secure' => $this->get_secure(),
					'httponly' => true,
					'samesite' => 'Strict'
                );
				if (explode('.', PHP_VERSION)[0] < 7){
					setcookie($this->get_cookie_name()."terms", true, time() +
                        (60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
					setcookie($this->get_cookie_name(), "", time() +
						(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
				}
				else{
					setcookie($this->get_cookie_name()."terms", true, $arr_cookie_options);
					setcookie($this->get_cookie_name(), "", $arr_cookie_options);
				}
				$gcotd_msg="Terms not accepted";
				$this->setOutput(self::$TERMS, $gcotd_msg);
			}
		}
		else
		{
			//file_put_contents($this->get_path()."/debug.log", "\nAuth setting cookie returning user_data", FILE_APPEND);

			$arr_cookie_options = array (
				'expires' => time() + (60 * 60 * 24 * 184),
				'path' => '/',
				'domain' => $this->get_domain(),
				'secure' => $this->get_secure(),
				'httponly' => true,
				'samesite' => 'Strict'
			);
			if (explode('.', PHP_VERSION)[0] < 7){
				setcookie($this->get_cookie_name(), $this->user_data['id'].".".$this->user_data['password'], time() +
					(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
			}
			else{
				setcookie($this->get_cookie_name(), $this->user_data['id'].".".$this->user_data['password'], $arr_cookie_options);
			}
			//update login time
			$date = date("Y-m-d H:i:s");
			$this->getDb()->query("UPDATE users SET last_login='".$date."' 
				WHERE id=".$this->user_data['id']); 
			$this->setOutput(self::$SUCCESS, $this->user_data);
		}
	}

	public function changePassword(){
		if($this->user_data['name'] == 'guest'){
			$this->setOutput(self::$FAIL, "You cannot do anything as Guest.");
		}
		else{
			$oldpassword = $_POST["oldpassword"];
			$newpassword = $_POST["newpassword"];
			$newpassword2 = $_POST["newpassword2"];
			$this->lookupUserInfo();
			if($newpassword2 == NULL OR $oldpassword == NULL OR $newpassword == NULL)
			{
				$gcotd_msg = "Please complete the form.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else if(!password_verify($oldpassword, $this->user_data['password'])){
				$gcotd_msg = "Your old password is incorrect.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else if($newpassword2 != $newpassword)
			{
				$gcotd_msg = "Your new and confirmed passwords do not match.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else if($oldpassword == $newpassword)
			{
				$gcotd_msg = "Your new password cannot equal your old one.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			else
			{
				if($this->user_data['email'] == $newpassword){
					$gcotd_msg="Your password cannot equal your email.";
					$this->setOutput(self::$FAIL, $gcotd_msg);
				} 
				else if($this->user_data['name'] == $newpassword){
					$gcotd_msg="Your password cannot equal your user name.";  
					$this->setOutput(self::$FAIL, $gcotd_msg);	
				} 
				else {
					$newpassword = password_hash($newpassword, PASSWORD_DEFAULT);
					$stmt = $this->getDb()->prepare(
						"update users set password=:password WHERE id=:user_id");
					$stmt->bindValue(':password',  $newpassword, PDO::PARAM_STR);
					$stmt->bindValue(':user_id',  intval($this->user_data['id']), PDO::PARAM_INT);
					$stmt->execute();
					
					if ($stmt->rowCount() > 0){
						$arr_cookie_options = array (
							'expires' => time() + (60 * 60 * 24 * 184),
							'path' => '/',
							'domain' => $this->get_domain(),
							'secure' => $this->get_secure(),
							'httponly' => true,
							'samesite' => 'Strict'
						);
						if (explode('.', PHP_VERSION)[0] < 7){
							setcookie($this->get_cookie_name(), $this->user_data['id'].".".$newpassword, time() +
								(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
						}
						else{
							setcookie($this->get_cookie_name(), $this->user_data['id'].".".$newpassword, $arr_cookie_options);
						}
						$gcotd_msg="Password Changed.";
						$this->setOutput(self::$SUCCESS, $gcotd_msg);
					} 
					else{
						$gcotd_msg="An error occurred updating your password, sorry.";
						$this->setOutput(self::$FAIL, $gcotd_msg);
					} 
				}
			}
		}
	}
	public function acceptCookies(){
		$accepted = $_POST["cookies_accepted"];
		if($accepted == '0'){
			$arr_cookie_options = array (
				'expires' => time() + (60 * 60 * 24 * 184),
				'path' => '/',
				'domain' => $this->get_domain(),
				'secure' => $this->get_secure(),
				'httponly' => true,
				'samesite' => 'Strict'
			);
			if (explode('.', PHP_VERSION)[0] < 7){
				setcookie($this->get_cookie_name(), $this->user_data['id'].".".$this->user_data['password'], time() +
					(60 * 60 * 24 * 184), "/; samesite=strict", $this->get_domain(), $this->get_secure(), 1); // 6 months
			}
			else{
				//foreach ($arr_cookie_options as $name => $value) {
					//file_put_contents($this->get_path()."/debug.log", "$name: $value\n", FILE_APPEND);
				//}
				setcookie($this->get_cookie_name(), $this->user_data['id'].".".$this->user_data['password'], $arr_cookie_options);
				//file_put_contents($this->get_path()."/debug.log", "Auth.php: setcookie successful\n", FILE_APPEND);
			}
			$this->setOutput(self::$SUCCESS, $this->user_data);
		}
	}
	public function acceptTerms(){
		$terms_accepted = $_POST["terms_accepted"];
		if($terms_accepted == '0'){
			$sql = "update users set terms_accepted=0 WHERE id=:user_id";
			try{
				$stmt = $this->getDb()->prepare($sql);
				$stmt->bindValue(':user_id',  intval($this->user_data['id']), PDO::PARAM_INT);
				$stmt->execute();
				
				if ($stmt->rowCount() > 0){
					$this->setOutput(self::$SUCCESS, $this->user_data);
				} 
				else{
					$gcotd_msg="An error occurred updating your status, sorry.";
					$this->setOutput(self::$TERMS, $gcotd_msg);
				}
			} catch(PDOException $ex) {
				$gcotd_msg="An error occurred, sorry.";
				$gcotd_msg.="\nAn Error occured running the following sql:".$sql;
				$gcotd_msg.="\n".$ex->getMessage();
				error_log($gcotd_msg);
				$this->setOutput(self::$TERMS, $gcotd_msg);
				throw new Exception();
			}
		} 
		else{
			$gcotd_msg.="Please click the check the box to accept the terms.";
			$this->setOutput(self::$TERMS, $gcotd_msg);
			print $this->printOutput();
			exit;
		} 
	}
}
?>