<?PHP
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/lib/Auth.php');
require_once(dirname(__FILE__).'/lib/Utils.php');
require_once(dirname(__FILE__).'/lib/password.php');
$auth = new Auth();
try{
	$auth->authenticate();
	if($auth->user_data['name'] == 'guest'){
		echo '{"status":"error", "msg":"You cannot do anything as Guest."}';
		exit;	
	}
	else{
	$utils = new Utils($auth);
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$id = $_GET["id"];
		$path = $utils->get_media_path()."/".$auth->user_data["name"]."/".$id;
		if(isset($_FILES['upl'])){
			$uploads = array();
			$errors = array();
			foreach($_FILES as $key0=>$FILES) {
				if(isset($_FILES[$key0])){
					foreach($FILES as $key=>$value) {
						foreach($value as $key2=>$value2) {
							$uploads[$key0][$key2][$key] = $value2;
						}
					}
				}
				else{
					$errors[]=$key0." not set.";
				}
			}
			$files = $uploads;
			foreach($files['upl'] as $file){
				$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
				$newFile = $utils->fixFileName($file['name']);
				if(($utils->isImage($newFile) || $utils->isVideo($newFile))
					&& ($file['size'] < 32000000 && $file['size'] > 0)){
						$newinfo = pathinfo($newFile);
						$newfile_name =  basename($newFile,'.'.$newinfo['extension']);
						$newFile = $newfile_name.'_'.getNewPassword().'.'.$newinfo['extension'];
					if(!@opendir($path)){
						mkdir($path, 0777, true);
					}
					if(move_uploaded_file($file['tmp_name'], $path.'/'.$newFile)){
						if($utils->isImage($newFile))
						{
							$newId = $utils->processImage($path, $newFile, $id);	
							if($newId){
								$utils->setMainImage($newFile, $id, false);
							}
						}
						else if($utils->isVideo($newFile))
						{
							$newId = $utils->processVideo($path, $newFile, $id);
							if($newId){
									$utils->setMainImage($newFile, $id, false);
							}
						}
					}
					else{
						$errors[] = 'Unable to move_uploaded_file to : '.$path.'/'.$newFile;
					}
				}else{
					$errors[] = $newFile.' not accepted.  Uploads must be images or MP4 videos and the entire size of the upload cannot exceed 30000000 bytes (30mb).';
				}
			}
			if(empty($errors) == false){
				echo '{"status":"error", "msg":'.json_encode($errors).'}';
			}
			else{
						echo '{"status":"success"}';
			}
			exit;
		}
		else{
			echo '{"status":"error", "msg":"Uploads must be images or MP4 videos and the entire size of the upload cannot exceed 30000000 bytes (30mb)."}';
			exit;	
		}
	}
	else{
		echo '{"status":"error", "msg":"Only POST accepted."}';
		exit;	
	}
	}
}
catch(Exception $ex){
	echo '{"status":"error", "msg":"upload.php: An Exception occurred."}';
	exit;
}
?>
