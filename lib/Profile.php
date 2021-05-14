<?PHP
/**
    GRLDCHZ - a PHP REST-like backing for a social network
	/grldservice/lib/Profile.php is part of GRLDCHZ
	
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
class Profile extends Connect{
	private $auth;
	private $postFirstName;
	private $postDesc;
	private $postLastName;
	private $postEmail;
	
	public $userName;
	public $userPage;
/*
	private $userId = $userRow['id'];
	private $userName = $userRow['name'];
	private $bannerImage = $userRow['banner_img'];
	private $bannerImageUrl = $auth->get_gcotd_home()."/getfile.php?media=media/".$userName."/".$bannerImage;
	private $bannerImageMarginTop = $userRow['banner_margin_top'];
	private $bannerImageInfo = $userRow['banner_json'];
	private $bannerThumbUrl = str_replace("profile_", "thumb_",$bannerImageUrl);
	private $profileImage = $userRow['img_file'];
	private $profileImageUrl = $auth->get_gcotd_home()."/getfile.php?media=media/".$userName."/".$profileImage;
	private $thumbImageUrl = str_replace("profile_", "thumb_",$profileImageUrl);
	private $profileImageWidth = $userRow['img_width'];
	private $profileImageHeight = $userRow['img_height'];
	private $profileImageMarginTop = $userRow['img_margin_top'];
	private $profileImageMarginLeft = $userRow['img_margin_left'];
	private $profileImageInfo = $userRow['img_json'];
	private $userImgCaption = $userRow['img_caption'];
	private $userDescription = stripslashes($userRow['description']);
	private $userSignUpDate = date(Auth::$DATE_DISPLAY_FORMAT, strtotime($userRow['create_date_time']));
	private $userFirstName = $userRow['first_name'];
	private $userLastName = $userRow['last_name'];
	private $userEmail = $userRow['email'];
	private $userDisplayName = ucfirst($userFirstName)." ".substr(ucfirst($userLastName), 0, 1).".";
*/
	
	public function __construct(Auth $auth){
		parent::__construct();
		$this->auth = $auth;
		$this->userName = $this->auth->user_data["name"];
		if(isset($_GET["userpage"]) && $_GET["userpage"] != null){
			$this->userPage = $_GET["userpage"];
		}
		if(isset($_POST["userdesc"]) && $_POST["userdesc"] != null){
			$this->postDesc = $_POST["userdesc"];
		}
		if(isset($_POST["firstname"]) && $_POST["firstname"] != null){
			$this->postFirstName = $_POST["firstname"];
		}
		if(isset($_POST["lastname"]) && $_POST["lastname"] != null){
			$this->postLastName = $_POST["lastname"];
		}
		if(isset($_POST["email"]) && $_POST["email"] != null){
			$this->postEmail = $_POST["email"];
		}
		
	}
	public function post(){
		if($this->auth->user_data['name'] == 'guest'){
			$this->setOutput(self::$FAIL, "You cannot do anything as Guest.");
		}
		else{
        if(!preg_match('/^[a-z\d_]{1,30}$/i', $this->postFirstName))
        {
            $gcotd_msg = "Your first name must be between 1 and 30 characters cannot contain spaces or special characters.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
        }
        else{
        if(!preg_match('/^[a-z\d_]{1,30}$/i', $this->postLastName))
        {
            $gcotd_msg = "Your last name must be between 1 and 30 characters cannot contain spaces or special characters.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
        }
        else{
	    if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $this->postEmail))
        { 
            $gcotd_msg = "Your email address is not valid.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
        }
		else{
	    $postDesc = addslashes($this->postDesc);
        $postDesc = str_replace("<script", "<br>", $postDesc);
        $postDesc = str_replace("<?", "<br>", $postDesc);
        $postDesc = str_replace("\n", "<br>", $postDesc);
		
		try{
			// construct update sql statement
			$sql = "update users set 
				description=:postDesc,
				first_name=:postFirstName,
				last_name=:postLastName,
				email=:postEmail
				where id=:userId";
			$stmt = $this->getDb()->prepare($sql);
			$stmt->bindValue(':postDesc',  $postDesc, PDO::PARAM_STR);
			$stmt->bindValue(':postFirstName',  $this->postFirstName, PDO::PARAM_STR);
			$stmt->bindValue(':postLastName',  $this->postLastName, PDO::PARAM_STR);
			$stmt->bindValue(':postEmail',  $this->postEmail, PDO::PARAM_STR);
			$stmt->bindValue(':userId',  intval($this->auth->user_data['id']), PDO::PARAM_INT);
			$stmt->execute();
		} catch(PDOException $ex) {
			$gcotd_msg="An Error occured running the following sql:".$sql;
			$gcotd_msg.=$ex->getMessage();
			error_log($gcotd_msg);
			$gcotd_msg="An error occurred posting your profile, sorry.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
		$gcotd_msg = "Profile successfully posted.";
		$this->setOutput(self::$SUCCESS, $gcotd_msg);
		}}}
		}
	}
	public function get(){
        if($this->userPage && !preg_match('/^[a-z\d_]{1,30}$/i', $this->userPage))
        {
            $gcotd_msg = "No userPage in the request.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
        }
        else if(!$this->userPage){
				$this->setOutput(self::$SUCCESS, $this->auth->user_data);
		}
		else{
			$users_sql = "SELECT first_name, last_name, create_date_time, description FROM users WHERE name = :userpage";
			$results = $this->getDb()->prepare($users_sql);
			$results->bindValue(':userpage',  trim($this->userPage), PDO::PARAM_STR);
			$results->execute();
			$total = $results->rowCount();		
			if($total == 0){
				$gcotd_msg="User not found";
				$this->setOutput(self::$FAIL, $gcotd_msg);
				throw new Exception($gcotd_msg);
			}
			else{
				$this->setOutput(self::$SUCCESS, $results->fetch(PDO::FETCH_ASSOC));
			}
		}
	}
	public function setProfileImage($file){
		try{
			// construct update sql statement
			$sql = "update users set 
				img_file=:img_file
				where id=:userId";
			$stmt = $this->getDb()->prepare($sql);
			$stmt->bindValue(':img_file', $file, PDO::PARAM_STR);
			$stmt->bindValue(':userId',  intval($this->auth->user_data['id']), PDO::PARAM_INT);
			$stmt->execute();
			$this->setOutput(self::$SUCCESS, $file);
		} catch(PDOException $ex) {
			$gcotd_msg="An Error occured running the following sql:".$sql;
			$gcotd_msg.=$ex->getMessage();
			error_log($gcotd_msg);
			$gcotd_msg="An error occurred posting your profile, sorry.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}		
	}
}
?>