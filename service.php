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
require_once(dirname(__FILE__).'/lib/Auth.php');
$auth = new Auth();
try{
	$auth->authenticate();
	require_once(dirname(__FILE__).'/lib/Comment.php');
	require_once(dirname(__FILE__).'/lib/Media.php');
	require_once(dirname(__FILE__).'/lib/Posts.php');
	require_once(dirname(__FILE__).'/lib/Profile.php');
	require_once(dirname(__FILE__).'/lib/Skillet.php');
	require_once(dirname(__FILE__).'/lib/Utils.php');

	if(isset($_GET["get"]) &&  $_GET["get"] == "posts"){
		$posts = new Posts($auth);
		$posts->getPosts();
		print $posts->printOutput();
	}
	else if(isset($_GET["get"]) &&  $_GET["get"] == "media"){
		$media = new Media($auth);
		$media->getMedia();
		print $media->printOutput();
	}
	else if(isset($_POST['changepass']))
	{
		$auth->changePassword();
		print $auth->printOutput();
	}
	else if(isset($_POST["caption"])){
		$media = new Media($auth);
		$media->postCaption();
		print $media->printOutput();
	}
	else if(isset($_POST["comment"])){
		$posts = new Posts($auth);
		$posts->post();
		print $posts->printOutput();
	}
	else if(isset($_POST["deleteid"])){
		$utils = new Utils($auth);
		if(isset($_POST["deleteid"])&&$_POST["deleteid"]!="" && is_numeric($_POST["deleteid"])){
			$id = $_POST["deleteid"];
			$utils->deleteComment($id);
			print $utils->printOutput();
		}
		else{
			$utils->setOutput(self::$FAIL, "id is missing or invalid");			
		}
	}
	else if(isset($_GET["get"]) &&  $_GET["get"] == "profile"){
		$profile = new Profile($auth);
		$profile->get();
		print $profile->printOutput();
	}
	else if(isset($_POST["userdesc"])){
		$profile = new Profile($auth);
		$profile->post();
		print $profile->printOutput();
	}
	else if(isset($_POST["skilletRequest"])){
		$skillet = new Skillet($auth);
		$skillet->skilletRequest();
		print $skillet->printOutput();
	}
	else if(isset($_POST["deletefile"])){
		$utils = new Utils($auth);
		if(isset($_POST["id"]) && $_POST["id"] != "" && is_numeric($_POST["id"])){
			
			$id = $_POST["id"];
			$path = $utils->get_media_path()."/".$auth->user_data["name"]."/".$id;
			$deleteFile = $_POST["deletefile"];
			$utils->deleteMedia($path, $deleteFile, $id);
		}
		else{
			$utils->setOutput(self::$FAIL, "id is missing or invalid");						
		}
		print $utils->printOutput();
	}
	else if(isset($_POST["deleteselected"])){
		$utils = new Utils($auth);
		$selectedArray = json_decode($_POST["selected"]);
		foreach($selectedArray as $selected){
			$splitted = split("/", $selected);
			$id = $splitted[0];
			$path = $utils->get_media_path()."/".$auth->user_data["name"]."/".$id;
			$deleteFile = $splitted[1];
			$utils->deleteMedia($path, $deleteFile, $id);
		}		
	}
	else if(isset($_POST["rotatefile"])){
		$utils = new Utils($auth);
		if(isset($_POST["id"]) && $_POST["id"] != "" && is_numeric($_POST["id"])){
			
			$id = $_POST["id"];
			$path = $utils->get_media_path()."/".$auth->user_data["name"]."/".$id;
			$file = $_POST["rotatefile"];
			$orientation = $_POST["orientation"];
			$utils->rotateMedia($path, $file, $id, $orientation);
		}
		else{
			$utils->setOutput(self::$FAIL, "id is missing or invalid");						
		}
		print $utils->printOutput();
	}
	else if(isset($_POST["reprocessFile"])){
		$utils = new Utils($auth);
		if(isset($_POST["id"]) && $_POST["id"] != "" && is_numeric($_POST["id"])){
			
			$id = $_POST["id"];
			$path = $utils->get_media_path()."/".$auth->user_data["name"]."/".$id;
			$file = $_POST["reprocessFile"];
			$utils->reprocessMedia($path, $file, $id);
		}
		else{
			$utils->setOutput(self::$FAIL, "id is missing or invalid");						
		}
		print $utils->printOutput();
	}
	else if(isset($_POST["rotateselected"])){
		$utils = new Utils($auth);
		$selectedArray = json_decode($_POST["selected"]);
		$orientation = $_POST["orientation"];
		foreach($selectedArray as $selected){
			$splitted = split("/", $selected);
			$id = $splitted[0];
			$path = $utils->get_media_path()."/".$auth->user_data["name"]."/".$id;
			$file = $splitted[1];
			$utils->rotateMedia($path, $file, $id);
		}		
	}
	else if(isset($_POST["skilletSearchTerm"])){
		$skillet = new Skillet($auth);
		if(isset($_POST["skilletUserId"])){
			$skillet->searchSkillets($_POST["skilletSearchTerm"], $_POST["skilletUserId"]);
		}
		else{
			$skillet->searchSkillets($_POST["skilletSearchTerm"], null);
		}
		print $skillet->printOutput();		
	}
	else if(isset($_POST["requestUser"])){
		$skillet = new Skillet($auth);
		$skillet->requestUser($_POST["requestUser"]);
		print $skillet->printOutput();		
	}
	else if(isset($_POST["removeUser"])){
		$skillet = new Skillet($auth);
		$skillet->removeUser($_POST["removeUser"]);
		print $skillet->printOutput();		
	}
	else if(isset($_POST["rejectUser"])){
		$skillet = new Skillet($auth);
		$skillet->removeUser($_POST["rejectUser"]);
		print $skillet->printOutput();		
	}
	else if(isset($_POST["hideUnhideUser"])){
		$skillet = new Skillet($auth);
		$skillet->hideUnhideUser($_POST["hideUnhideUser"]);
		print $skillet->printOutput();		
	}
	else if(isset($_POST["acceptUser"])){
		$skillet = new Skillet($auth);
		$skillet->acceptUser($_POST["acceptUser"]);
		print $skillet->printOutput();		
	}
	else if(isset($_POST["updateNumHits"])){
		$utils = new Utils($auth);
		$utils->updateNumHits($_POST["content_id"], $_POST["mediafile"]);
		print $utils->printOutput();
	}
	else if(isset($_POST["profile_img"])){
		$content_id = $_POST["content_id"];
		$file = $_POST["profile_img"];
		$profile = new Profile($auth);
		if(isset($_POST["unset"]) && $_POST["unset"] == "true"){
			$profile->setProfileImage("");
		}
		else{
			$profile->setProfileImage($content_id."/img_profile_".$file.".jpeg");
		}
		print $profile->printOutput();		
	}
	else if(isset($_POST["main_img"])){
		$content_id = $_POST["content_id"];
		$file = $_POST["main_img"];
		$utils = new Utils($auth);
		if(isset($_POST["unset"]) && $_POST["unset"] == "true"){
			$utils->setMainImage("", $content_id, true);
		}
		else{
			$utils->setMainImage($file, $content_id, true);
		}
		print $utils->printOutput();		
	}
}
catch(Exception $e){
	$auth->setOutput(Auth::$FAIL, $e->getMessage());
	print $auth->printOutput();
}
?>