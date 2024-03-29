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
require_once(dirname(__FILE__).'/lib/VideoStream.php');
require_once(dirname(__FILE__).'/lib/mime_content_type.php');
require_once(dirname(__FILE__).'/lib/Auth.php');
require_once(dirname(__FILE__).'/lib/Skillet.php');
require_once(dirname(__FILE__).'/lib/Utils.php');
$auth = new Auth();
try{
	$auth->authenticate();
	$utils = new Utils($auth);
	$file = $_GET["media"];
	$hitcounter = $_GET["hitcounter"];
	$mediaArr = explode("/", $file);
	$skillet = new Skillet($auth);
	$check_user_skillet = $skillet->checkSkilletWithUserName(trim($mediaArr[1]), trim($mediaArr[2]));
	
	if(!$check_user_skillet)
	{
		$auth->setOutput(Auth::$FAIL, "This media is private.");
		throw new Exception();
	}
	$contentDisposition = "inline";
	if(isset($_GET["original"]))
	{
		$contentDisposition = "attachment";
	}
	$path = $utils->get_path()."/".$file;
	if(file_exists($path)){
        header("Pragma: public");
        header("Content-Disposition: ".$contentDisposition."; filename=".basename($file));
        header("Content-Transfer-Encoding: binary");
		if (isset($_SERVER['HTTP_RANGE']))  { 
			//$headerRangeValue = $_SERVER['HTTP_RANGE'];
			//if($headerRangeValue == "bytes=0-" || $headerRangeValue == "bytes=0-1"){
			//	$utils->updateNumHits($mediaArr[2], $mediaArr[3]);
				//file_put_contents($auth->get_path()."/debug.log", "headerRangeValue: $headerRangeValue\n", FILE_APPEND);
			//}
            $stream = new VideoStream($file);
            $stream->start();
		}
		else {
			$utils->updateNumHits($mediaArr[2], $mediaArr[3]);
            header("Cache-Control: max-age=2592000, public");
            header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
			header("Content-Length: ".filesize($file));
            header("Content-Type:  ".mime_content_type($file));
			readfile($file);
		}
	}
	else{
		error_log("file does not exist: ".$file);
	}
}
catch(Exception $ex){
	print $auth->printOutput();
}
?>