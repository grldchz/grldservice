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
require_once(dirname(__FILE__).'/password.php');
require_once(dirname(__FILE__).'/Config.php');
class Connect extends Config{
	public static $DATE_DISPLAY_FORMAT = "D M j Y g:i A";
	public static $SUCCESS = "SUCCESS";
	public static $FAIL = "FAIL";
	public static $TERMS = "TERMS";
	public static $PERMS = "PERMS";
	private $output;
	private $db;
	public function __construct(){
		parent::__construct();
		$this->connect();
	}
	public function getDb(){
		if($this->db == null){
			$this->connect();
		}
		return $this->db;
	}
	public function connect(){
		$this->db = new PDO('mysql:host='.$this->get_mysql_url()
			.';dbname='.$this->get_mysql_database()
			.';charset=utf8', $this->get_mysql_user(), $this->get_mysql_password());
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	public function getBannerImgJson(){
		$adminSql = "select * from users where name='".$this->get_admin_user()."'";
		$adminQuery = $this->db->query($adminSql);
		$adminRow = $adminQuery->fetch(PDO::FETCH_ASSOC);
		return $adminRow["banner_json"];		
	}
	public function setOutput($status, $data){
		if($status != self::$SUCCESS){
			$this->output = array(
				'status'=>$status,
				'msg'=>$data);
		}
		else{
			$this->output = $data;
		}	
	}
	public function getOutput(){
		return $this->output;
	}
	public function printOutput(){
		return json_encode($this->getOutput());
	}
	public function verifyRecaptcha($captcha){
		$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".
			$this->get_captcha_private_key()."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
		return json_decode($response);
	}
}
?>