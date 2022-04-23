<?php
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/lib/rangeDownload.php');
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
		// do it for any device that supports byte-ranges not only iPhone
		if (isset($_SERVER['HTTP_RANGE']))  { 
			$headerRangeValue = $_SERVER['HTTP_RANGE'];
			if($headerRangeValue == "bytes=0-" || $headerRangeValue == "bytes=0-1"){
				$utils->updateNumHits($mediaArr[2], $mediaArr[3]);
				file_put_contents("headers.log", "headerRangeValue: $headerRangeValue\n", FILE_APPEND);
			}
			//rangeDownload($file);
		}
		else {
			$utils->updateNumHits($mediaArr[2], $mediaArr[3]);
			/*
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Disposition: ".$contentDisposition."; filename=".basename($file));
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($file));
			readfile($file);
			*/
		}
		header("Content-Type:  ".mime_content_type($file));
		rangeDownload($file);
	}
	else{
		error_log("file does not exist: ".$file);
	}
}
catch(Exception $ex){
	print $auth->printOutput();
}
?>