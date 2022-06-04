<?PHP
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/Connect.php');
require_once(dirname(__FILE__).'/Posts.php');
require_once(dirname(__FILE__).'/password.php');
class Utils extends Connect{
	private $auth;
	// construct an instance of this class 
	function __construct(Auth $auth){
		parent::__construct();
		$this->auth = $auth;
		putenv('LD_LIBRARY_PATH=/home/grilledc/lib');
		putenv('PKG_CONFIG_PATH=/home/grilledc/lib/pkgconfig');		
	}

	public function isDirExists($statusId, $statusUserId){
		$statusIdIntVal=intval($statusId);
		$path = $this->get_media_path()."/$statusUserId/$statusId";
		if($handle = @opendir($path)){
			$photos[] = null;
			$totalPics=0;
			$totalVids=0;
			while (false !== ($file = readdir($handle))) {
				if($file != null && $file != "." && $file != ".."){
					if(stripos($file, ".WMV") === false){
						$totalPics++;
					}
					else{
						$totalVids++;
					}
				}
				$photos[] = $file;
			}
			closedir($handle);
			$totalFiles = $totalPics + $totalVids;
			if($totalFiles>0){
				return true;
			}
		}
		return false;
	}
	public function isVideo($file){
		//$extArray = array('mpg', 'mov', 'flv', 'm4v', 'mp4', 'avi', 'wmv', 'ogv', 'webm', 'qt', 'rm', '3gp');
		$extArray = array('mp4');
		$parts = pathinfo($file);
		$ext = $parts ['extension'];
		$video = false;
		if(in_array(strtolower($ext), $extArray)){
			$video = true;
		}
		return $video;
	}
	public function isImage($file){
		$extArray = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'tif', 'tiff');
		$parts = pathinfo($file);
		$ext = $parts ['extension'];
		$image = false;
		if(in_array(strtolower($ext), $extArray)){
			$image = true;
		}
		return $image;
	}
	public function createJpeg($path, $mediaFile){
		$file = $path."/".$mediaFile;
        $output_filename = $path."/img_thumb_".$mediaFile.".jpeg";
		$cmd = $this->get_imagemagick()." -define jpeg:size=600x450 ".
			$file." -auto-orient -thumbnail 100x80 -unsharp 0x.5 ".$output_filename;
		exec($cmd);
        $output_filename = $path."/img_profile_".$mediaFile.".jpeg";
		$cmd = $this->get_imagemagick()." -define jpeg:size=600x450 ".
			$file." -auto-orient -thumbnail 200x150 -unsharp 0x.5 ".$output_filename;
		exec($cmd);
        $output_filename = $path."/img_slide_".$mediaFile.".jpeg";
		$cmd = $this->get_imagemagick()." -define jpeg:size=1600x1200 ".
			$file." -auto-orient -thumbnail 600x450 -unsharp 0x.5 ".$output_filename;
		exec($cmd);
        $output_filename = $path."/img_full_".$mediaFile.".jpeg";
		$cmd = $this->get_imagemagick()." -define jpeg:size=1600x1200 ".
			$file." -auto-orient -thumbnail 1600x1200 -unsharp 0x.5 ".$output_filename;        
		exec($cmd);
	}
	public function createThumb($path, $mediaFile){
		$file = $path."/img_full_".$mediaFile.".jpeg";
		$imgDims = $this->getMediaDimensions($path, $mediaFile);
		$thumb   = $this->getThumbDimensions($imgDims["width"], $imgDims["height"]);
		$output_filename = $path."/img_thumb_".$mediaFile.".jpeg";
		exec($this->get_ffmpeg()." -i ".$file." -s ".$thumb." ".$output_filename);
	}
	public function getMediaDimensions($path, $mediaFile) {
		$ffprobe_cmd = $this->get_ffprobe().' -show_format -show_streams '
		.$path.'/'.$mediaFile;
		exec('echo "'.$ffprobe_cmd.'" > '.$path.'/src/ffprobe_'.$mediaFile.'.log 2>&1');
		exec($ffprobe_cmd.' > '.$path.'/src/ffprobe_'.$mediaFile.'.json');
		$ffprobe_output = file_get_contents($path.'/src/ffprobe_'.$mediaFile.'.json');
		exec('echo "'.$ffprobe_output.'" >> '.$path.'/src/ffprobe_'.$mediaFile.'.log');
		$ffprobe_json = json_decode($ffprobe_output, true);
		$streams = $ffprobe_json["streams"];
		if($streams != null)
		{
			$videoStreamCount = 0;
			$mainVidStreamIdx = 0;
			foreach($streams as $stream)
			{
				if($stream["codec_type"]=="video")
				{
					$videoStreamCount++;
					if($stream["profile"] != null)
					{
						if(stripos($stream["profile"], "Main") >= 0 ||
						stripos($stream["profile"], "Baseline") >= 0)
						{
							$mainVidStreamIdx = $stream["index"];
						}
					}
					$width = $stream["width"];
					$height = $stream["height"];
				}
					
			}
		}
		else
		{
		$ffprobe_cmd = $this->get_ffprobe().' -show_format -show_streams '
		.$path.'/'.$mediaFile;
		exec('echo "'.$ffprobe_cmd.'" > '.$path.'/src/ffprobe_'.$mediaFile.'.log 2>&1');
		exec($ffprobe_cmd.' > '.$path.'/src/ffprobe_'.$mediaFile.'.json');
		$ffprobe_output = file_get_contents($path.'/src/ffprobe_'.$mediaFile.'.json');
		exec('echo "'.$ffprobe_output.'" >> '.$path.'/src/ffprobe_'.$mediaFile.'.log');

			exec($this->get_ffprobe().' -show_format -show_streams '
			.$path.'/'.$mediaFile, $ffprobe_exec);
		
			foreach($ffprobe_exec as $ffprobe_line){
				if(stripos($ffprobe_line, "width") === 0)
				{
					$width = $ffprobe_line;
				}
				else if(stripos($ffprobe_line, "height") === 0)
				{
					$height = $ffprobe_line;
				}
			}
			$wvals = (explode ( '=', $width ));
			$width = $wvals[1];
			$hvals = (explode ( '=', $height ));
			$height = $hvals[1];
		}
		return array ('width' => $width, 'height' => $height, 'index' => $mainVidStreamIdx );
	}
	public function getWidthHeight($origWidth, $origHeight, $maxWidth, $maxHeight){
		$ret = $origWidth."x".$origHeight;
		if ($origWidth >= $origHeight)
		{
			if ($origWidth <= $maxWidth && $origHeight <= $maxHeight){
				return $ret;  // no resizing required
			}
			$wRatio = $maxWidth / $origWidth;
			$hRatio = $maxHeight / $origHeight;
		}
		else
		{
			if ($origHeight <= $maxWidth && $origWidth <= $maxHeight){
				return $ret; // no resizing required
			}
			$wRatio = $maxHeight / $origWidth;
			$hRatio = $maxWidth / $origHeight;
		}

		// hRatio and wRatio now have the scaling factors for height and width.
		// You want the smallest of the two to ensure that the resulting image
		// fits in the desired frame and maintains the aspect ratio.
		$resizeRatio = min($wRatio, $hRatio);

		$newHeight = $origHeight * $resizeRatio;
		$newWidth = $origWidth * $resizeRatio;
		$ret = round($newWidth)."x".round($newHeight);
		return $ret;
	}
	function getFileSize($a_bytes)
	{
		if ($a_bytes < 1024) {
			return $a_bytes .' B';
		} elseif ($a_bytes < 1048576) {
			return round($a_bytes / 1024, 2) .' KiB';
		} elseif ($a_bytes < 1073741824) {
			return round($a_bytes / 1048576, 2) . ' MiB';
		} elseif ($a_bytes < 1099511627776) {
			return round($a_bytes / 1073741824, 2) . ' GiB';
		} elseif ($a_bytes < 1125899906842624) {
			return round($a_bytes / 1099511627776, 2) .' TiB';
		} elseif ($a_bytes < 1152921504606846976) {
			return round($a_bytes / 1125899906842624, 2) .' PiB';
		} elseif ($a_bytes < 1180591620717411303424) {
			return round($a_bytes / 1152921504606846976, 2) .' EiB';
		} elseif ($a_bytes < 1208925819614629174706176) {
			return round($a_bytes / 1180591620717411303424, 2) .' ZiB';
		} else {
			return round($a_bytes / 1208925819614629174706176, 2) .' YiB';
		}
	}
	function getThumbDimensions($origWidth, $origHeight)
	{
		return $this->getWidthHeight($origWidth, $origHeight, "100", "80");
	}
	function getProfileDimensions($origWidth, $origHeight)
	{
		return $this->getWidthHeight($origWidth, $origHeight, "200", "150");
	}
	function getSlideDimensions($origWidth, $origHeight)
	{
		return $this->getWidthHeight($origWidth, $origHeight, "600", "450");
	}
	function getFullDimensions($origWidth, $origHeight)
	{
		return $this->getWidthHeight($origWidth, $origHeight, "1600", "1200");
	}
	public function insertMedia($file, $contentId)
	{
		$sql = "insert into media (content_id, file, title)
            values('".$contentId."', '".$file."', '".$file."')";
			
		try{
			$mediaQuery = $this->getDb()->query($sql);
		} catch(PDOException $ex) {
			error_log($ex->getMessage());
		}		
		return $this->getDb()->lastInsertId();
	}
	public function errorHandler($errno, $errmsg, $errfile){
		
	}
	public function getCaption($file, $contentId)
	{
        $trueFile = $this->getSrcFileName($file);
		$photoCaption = $trueFile;
		$sql = "select * from media where
		content_id='".$contentId."' and
		file='".$trueFile."'";
		$stmt = $this->getDb()->prepare($sql);
		$stmt->execute();
		if($stmt->rowCount() === 1){
			$mediaRecord = $stmt->fetch(PDO::FETCH_ASSOC);
			$photoCaption = $mediaRecord["title"];
		}
		return $photoCaption;
	}
	public function getSrcFileName($mediaFile)
	{
        $parts = pathinfo($mediaFile);
        $ext = ".".$parts ['extension'];
		$srcFileName = substr($mediaFile, 10, strripos($mediaFile, $ext)-strlen($mediaFile));
        return $srcFileName;
	}
	public function fixFileName($file)
	{
        $info = pathinfo($file);
        $file_name =  basename($file,'.'.$info['extension']);
        $mediaFile = preg_replace('#\W#', '', $file_name).'.'.$info['extension'];
		return $mediaFile;
	}
	public function processImage($path, $file, $contentId)
	{
		$mediaFile = $this->fixFileName($file);
		@rename($path.'/'.$file, $path.'/'.$mediaFile);
		$this->createJpeg($path, $mediaFile);
		if(!@opendir($path."/src")){
			mkdir($path."/src", 0777, true);
		}
		@rename($path.'/'.$mediaFile, $path.'/src/'.$mediaFile);
		if (file_exists($path."/img_thumb_".$mediaFile.".jpeg")) {
			$newId = $this->insertMedia($mediaFile, $contentId); 
			$this->getDb()->query('update contents set num_photos = num_photos + 1 where id='.$contentId);
        }
		return $newId;		
	}

	public function processVideo($path, $file, $contentId)
	{
        $mediaFile = $this->fixFileName($file);
		if(!@opendir($path."/src")){
			mkdir($path."/src", 0777, true);
		}
		//rename($path.'/'.$file, $path.'/'.$mediaFile);

		//$this->processMp4($path, $mediaFile);
		//sleep(2);
		//rename($path.'/'.$mediaFile, $path.'/src/'.$mediaFile);
		//$this->processVideoThumb($path, $mediaFile);
		rename($path.'/'.$file, $path.'/proxy_mp4_'.$mediaFile.'.mp4');
		if (file_exists($path.'/proxy_mp4_'.$mediaFile.'.mp4')) {
			$this->connect();
			$newId = $this->insertMedia($mediaFile, $contentId);  
			$this->updateNumVideos($contentId);
		}
		return $newId;		
	}
	public function processWebm($path, $mediaFile){
		$imgDims = $this->getMediaDimensions($path, $mediaFile);
		$slide   = $this->getSlideDimensions($imgDims["width"], $imgDims["height"]);
		$mapOption = " ";
		if($imgDims["index"] != null && $imgDims["index"] > 0)
		{
			$mapOption = " -map 0:".$imgDims["index"]." -map 0:a ";
		}
		$webm_cmd = $this->get_ffmpeg().' -i '.$path.'/'.$mediaFile
		.$mapOption
		.'-s '.$slide.' -ar 44100 -b 1000k '
		.$path.'/proxy_med_'.$mediaFile.'.webm >> '
		.$path.'/src/ffmpeg_'.$mediaFile.'.log 2>&1';
		exec('echo "'.$webm_cmd.'" >> '.$path.'/src/ffmpeg_'.$mediaFile.'.log');
		exec($webm_cmd);
	}
	public function processMp4($path, $mediaFile){
		$mp4_cmd = $this->get_ffmpeg().' -i '.$path.'/'.$mediaFile
			.' '.$this->get_ffmpeg_args().' '
		.$path.'/proxy_mp4_'.$mediaFile.'.mp4 >> '
		.$path.'/src/ffmpeg_'.$mediaFile.'.log 2>&1';
		exec('echo "'.$mp4_cmd.'" >> '.$path.'/src/ffmpeg_'.$mediaFile.'.log');
		exec($mp4_cmd);		
	}
	public function processQt($path, $mediaFile){
		$qt_cmd = $this->get_qt_faststart().' '
		.$path.'/proxy_qt_'.$mediaFile.'.mp4 '
		.$path.'/proxy_mp4_'.$mediaFile.'.mp4 >> '
		.$path.'/src/ffmpeg_'.$mediaFile.'.log 2>&1';
		exec('echo "'.$qt_cmd.'" >> '.$path.'/src/ffmpeg_'.$mediaFile.'.log');
		exec($qt_cmd);
	}
	public function updateNumVideos($contentId){
		$this->getDb()->query('update contents set num_videos = num_videos + 1 where id='.$contentId);		
	}
	public function updateNumHits($contentId, $mediaFile){
		if(stripos($mediaFile, "img_full_") === 0){
			$parts = pathinfo($mediaFile);
			$ext = ".".$parts ['extension'];
			$srcFileName = substr($mediaFile, 9, strripos($mediaFile, $ext)-strlen($mediaFile));
			$query = 'update media set num_hits = num_hits + 1 where content_id='.$contentId.' and file=\''.$srcFileName.'\'';
			$this->getDb()->query($query);	
		}		
		/*else if(stripos($mediaFile, "img_slide_") === 0){
			$parts = pathinfo($mediaFile);
			$ext = ".".$parts ['extension'];
			$srcFileName = substr($mediaFile, 10, strripos($mediaFile, $ext)-strlen($mediaFile));
			$query = 'update media set num_hits = num_hits + 1 where content_id='.$contentId.' and file=\''.$srcFileName.'\'';
			$this->getDb()->query($query);	
		}*/	
		else if(stripos($mediaFile, "proxy_mp4_") === 0){
			//file_put_contents($this->get_path()."/debug.log", "mediaFile: $mediaFile\n", FILE_APPEND);
			$parts = pathinfo($mediaFile);
			$ext = ".".$parts ['extension'];
			$srcFileName = substr($mediaFile, 10, strripos($mediaFile, $ext)-strlen($mediaFile));
			$query = 'update media set num_hits = num_hits + 1 where content_id='.$contentId.' and file=\''.$srcFileName.'\'';
			$this->getDb()->query($query);	
		}		
	}
	public function processVideoThumb($path, $mediaFile)
	{
		$imgDims = $this->getMediaDimensions($path, 'proxy_mp4_'.$mediaFile.'.mp4');
		$thumb   = $this->getSlideDimensions($imgDims["width"], $imgDims["height"]);
                $profile   = $this->getProfileDimensions($imgDims["width"], $imgDims["height"]);

		$vid_frame_cmd = $this->get_ffmpeg().' -i '.$path.'/'.'proxy_mp4_'.$mediaFile.'.mp4'
		.' '
		.'-s '.$thumb.' -vframes 1 -ss 5 '
		.$path.'/vid_frame_'.$mediaFile.'.jpeg >> '
		.$path.'/src/ffmpeg_'.$mediaFile.'.log 2>&1';
		exec('echo "'.$vid_frame_cmd.'" >> '.$path.'/src/ffmpeg_'.$mediaFile.'.log');
		exec($vid_frame_cmd);

		$cmd = $this->get_ffmpeg().' -i '.$path.'/'.'proxy_mp4_'.$mediaFile.'.mp4'
		.' '
		.'-s '.$profile.' -vframes 1 -ss 5 '
		.$path.'/img_profile_'.$mediaFile.'.jpeg >> '
		.$path.'/src/ffmpeg_'.$mediaFile.'.log 2>&1';
		exec('echo "'.$cmd.'" >> '.$path.'/src/ffmpeg_'.$mediaFile.'.log');
		exec($cmd);

	}
	public function rotateMedia($path, $trueFile, $contentId, $orientation){
		$newFile = $trueFile;
		if (file_exists($path."/src/" . $newFile))
		{
			$newinfo = pathinfo($newFile);
			$newfile_name =  basename($newFile,'.'.$newinfo['extension']);
			$newFile = $newfile_name.'_'.getNewPassword().'.'.$newinfo['extension'];
		}
		
		if($this->isImage($newFile))
		{
            $cmd = $this->get_imagemagick()." -rotate 90 ".$path."/src/".$trueFile." ".$path."/".$newFile;
			exec($cmd);
			$newId = $this->processImage($path, $newFile, $contentId);
			$newRotatedFile = "img_thumb_".$newFile.".jpeg";
		}
		else if($this->isVideo($newFile))
		{
			$rotate_cmd = $this->get_rotate_video_cmd($path.'/src/'.$trueFile, $path.'/'.$newFile);
			exec('echo "'.$rotate_cmd.'" >> '.$path.'/src/rotate_'.$newFile.'.log');
			exec($rotate_cmd.' >> '.$path.'/src/rotate_'.$newFile.'.log 2>&1');
            $newId = $this->processVideo($path, $newFile, $contentId);
			$newRotatedFile = 'proxy_mp4_'.$newFile.'.mp4';
		}
		if (file_exists($path.'/'.$newRotatedFile)) {
			$this->deleteMedia($path, $trueFile, $contentId);
		}
		$this->setOutput(self::$SUCCESS, $newId);
	}
	public function reprocessMedia($path, $trueFile, $contentId){
		$newFile = $trueFile;
		if (file_exists($path."/src/" . $newFile))
		{
			$newinfo = pathinfo($newFile);
			$newfile_name =  basename($newFile,'.'.$newinfo['extension']);
			$newFile = $newfile_name.'_'.getNewPassword().'.'.$newinfo['extension'];
			if(copy($path."/src/".$trueFile, $path."/".$newFile)){
				if($this->isImage($newFile))
				{
					$newId = $this->processImage($path, $newFile, $contentId);
					$reprocessedFile = "img_thumb_".$newFile.".jpeg";
				}
				else if($this->isVideo($newFile))
				{
					$newId = $this->processVideo($path, $newFile, $contentId);
					$reprocessedFile = 'proxy_mp4_'.$newFile.'.mp4';
				}
				if (file_exists($path.'/'.$reprocessedFile)) {
					$this->deleteMedia($path, $trueFile, $contentId);
				}
				$this->setOutput(self::$SUCCESS, $newId);
				
			}
			else{
				error_log("no copy was made");
			}
		}
		
	}
	public function deleteMedia($path, $srcFile, $id){
		try{
		//$srcFile = $this->getSrcFileName($deleteFile);
		$stmt = $this->getDb()->prepare("select m.id, m.content_id, m.file from media m
			join contents c on c.id = m.content_id and c.user_name=:thisUserName		
			where m.content_id=:id and m.file=:srcFile");
		$stmt->bindValue(':id',  intval($id), PDO::PARAM_INT);
		$stmt->bindValue(':srcFile',  $srcFile, PDO::PARAM_STR);
		$stmt->bindValue(':thisUserName',  $this->auth->user_data['name'], PDO::PARAM_STR);
		$stmt->execute();
		if($stmt->rowCount() === 1){
			$deleteRecord = $stmt->fetch(PDO::FETCH_ASSOC);
			// delete file in db
			$stmt = $this->getDb()->prepare("update media set deleted=1 where id = :id");
			$stmt->bindValue(':id',  intval($deleteRecord["id"]), PDO::PARAM_INT);
			$stmt->execute();
			// update photo count
			if ($this->isImage($srcFile)){
				$stmt = $this->getDb()->prepare("update contents 
					set num_photos = num_photos - 1 
					where id=:id");
				$stmt->bindValue(':id',  intval($id), PDO::PARAM_INT);
				$stmt->execute();
			}
			// update video count
			else if ($this->isVideo($srcFile)){
				$stmt = $this->getDb()->prepare("update contents 
					set num_videos = num_videos - 1 where id=:id");
				$stmt->bindValue(':id',  intval($id), PDO::PARAM_INT);
				$stmt->execute();
			}
			// delete the image entry on the main content record
			$stmt = $this->getDb()->prepare("select * from contents 
				where id = :id");
			$stmt->bindValue(':id',  intval($id), PDO::PARAM_INT);
			$stmt->execute();
			if($stmt->rowCount() === 1){
				$contentRecord = $stmt->fetch(PDO::FETCH_ASSOC);
				if(stripos($contentRecord["image"], $srcFile) > 0){
					$stmt = $this->getDb()->prepare("update contents 
						set image = null 
						where id=:id");
					$stmt->bindValue(':id',  intval($id), PDO::PARAM_INT);
					$stmt->execute();
				}
			}			
			// delete file on file system, maybe future undelete feature
			/*
			if ($handle = opendir($path)) {
				while (false !== ($file = readdir($handle))) {
					if ($file===$deleteFile) {
						unlink($path."/".$file);
					}
				}
				closedir($handle);
			}
			*/
			$this->setOutput(self::$SUCCESS, "Deleted File: ".$srcFile);
		}
		else{
			$this->setOutput(self::$FAIL, $srcFile." does not exist");				
		}
		}
		catch(PDOException $ex) {
			$gcotd_msg.="\n<br>An Error occured running the following sql:".$sql;
			$gcotd_msg.="\n<br>".$ex->getMessage();
			error_log($gcotd_msg);
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
	}
	public function deleteComment($id){
		try{
			$sql = "update contents set deleted=1 where id=:id and user_name=:thisUserName";
		$stmt = $this->getDb()->prepare($sql);	
		$stmt->bindValue(':id',  intval($id), PDO::PARAM_INT);
		$stmt->bindValue(':thisUserName',  $this->auth->user_data['name'], PDO::PARAM_STR);
		$stmt->execute();
		$total = $stmt->rowCount();
		if($total == 0){
			throw new PDOException("id=".$id.", user_name=".$this->auth->user_data['name']);
		}
		else{			
			$this->setOutput(self::$SUCCESS, "Deleted Comment: ".$id);
		}
		} catch(PDOException $ex) {
			$gcotd_msg.="\n<br>An Error occured running the following sql:".$sql;
			$gcotd_msg.="\n<br>".$ex->getMessage();
			error_log($gcotd_msg);
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
	}
	public function setMainImage($image, $id, $overwrite){
		$grldPosts = new Posts($this->auth, $id);
		$grldPosts->getPosts();
		$output = $grldPosts->getOutput();
		$post = $output["results"][0];
		if($post["image"] == null || $overwrite == true){
			$mainImage = "";
			if($image != ""){
				$mainImage = $id."/img_profile_".$image.".jpeg";
			}
			try{
				$stmt = $this->getDb()->prepare("update contents set
					image=:image, image_title=:imageTitle 
					where id=:id");
				$stmt->bindValue(':image',  $mainImage, PDO::PARAM_STR);
				$stmt->bindValue(':imageTitle',  $mainImage, PDO::PARAM_STR);
				$stmt->bindValue(':id',  intval($id), PDO::PARAM_INT);
				$stmt->execute();
				$this->setOutput(self::$SUCCESS, $image);
			
			} catch(PDOException $ex) {
				$gcotd_msg="An Error occured running the following sql:".$sql;
				$gcotd_msg.=$ex->getMessage();
				error_log($gcotd_msg);
				$gcotd_msg="An error occurred.  Sorry.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}		
		}
	}
	public function updateSitemap($contentId, $mediaId, $remove){
		$protocol = $this->get_secure()==1?"https://":"http://";
		$domain = $this->get_domain()?$this->get_domain():"localhost";
		$contentIdUrl = $protocol."www.".$domain.$this->get_ui_context()."/content/".$contentId."/";
		$url = $protocol."www.".$domain.$this->get_ui_context()."/content/".$contentId;
		if($mediaId){
			$url.="/".$mediaId;
		}
		$url.="/index.php";
		$file = "../".$this->get_ui_context()."/sitemap.txt";
		$contents = file_get_contents($file);
		$escapedUrl = preg_quote($url, '/');
		$pattern = "/^.*$escapedUrl.*\$/m";
		//file_put_contents($this->get_path()."/debug.log", "\nUtils.php: updateSitemap; contentId=$contentId; mediaId=$mediaId; remove=$remove", FILE_APPEND);
		if(preg_match_all($pattern, $contents, $matches)){
			if($remove == 1){
				$contentIdUrl = preg_quote($contentIdUrl, '/');
				$pattern = "/\n$contentIdUrl.*\$/m";
				//file_put_contents($this->get_path()."/debug.log", "\nUtils.php: updateSitemap; pattern=$pattern", FILE_APPEND);
				$contents = preg_replace($pattern, '', $contents);
				file_put_contents($file, $contents);
			}
		}
		else{
			file_put_contents($file, "\n$url", FILE_APPEND);
		}

	}
}
?>
