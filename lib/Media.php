<?php
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/Utils.php');
class Media extends Utils{
	private static $MEDIA_COLS = array( 	
		'id',
        'content_id',		
		'user_name',
		'file',
		'title',
		'deleted'
	);
	private $media_id;
	private $content_id;
	private $sortParams;
	private $start;
	private $limit;
	public function __construct(){
		$num = func_num_args();
		$args = func_get_args();
		switch($num){
			case 3:
				$this->__call('__construct3', $args);
				break;
			case 2:
				$this->__call('__construct2', $args);
				break;
			default:
				$this->__call('__construct1', $args);
		}
    }
	public function __call($name, $arg){
		return call_user_func_array(array($this, $name), $arg);
	}
	
	public function __construct1(Auth $auth){
		parent::__construct($auth);
		$this->auth = $auth;
		if(isset($_GET["media_id"]) && $_GET["media_id"] != null){
			$this->media_id = $_GET["media_id"];
		}	
		if(isset($_GET["content_id"]) && $_GET["content_id"] != null){
			$this->content_id = $_GET["content_id"];
		}
		if(isset($_GET["sort"]) && $_GET["sort"] != null){
			$this->sortParams = $_GET["sort"];
		}
		if(isset($_GET["start"]) && $_GET["start"] != null){
			$this->start = $_GET["start"];
			$this->limit = $_GET["limit"];
		}
		if(isset($_GET["searchTerm"]) && $_GET["searchTerm"] != null){
			$this->searchTerm = $_GET["searchTerm"];
		}
		if(isset($_GET["fromDate"]) && $_GET["fromDate"] != null){
			$this->fromDate = $_GET["fromDate"];
		}
		if(isset($_GET["toDate"]) && $_GET["toDate"] != null){
			$this->toDate = $_GET["toDate"];
		}
	}
	public function __construct2(Auth $auth, $content_id){
		parent::__construct($auth);
		$this->auth = $auth;
		$this->content_id = $content_id;
	}
	public function __construct3(Auth $auth, $content_id, $media_id){
		parent::__construct($auth);
		$this->auth = $auth;
		$this->content_id = $content_id;
		$this->media_id = $media_id;
	}
	public function getMedia(){
		if($this->content_id != null){
			$media_sql = "select SQL_CALC_FOUND_ROWS m.id,m.content_id,c.user_name,m.file,m.title,m.num_hits,c.open_public 
				from media m 
				inner join contents c on c.id=m.content_id and c.deleted=0 and c.parent_id=0
				join users u on c.user_name=u.name
				inner join skillet s on
					(s.user_id = '".$this->auth->user_data['id']."' and u.id = s.friend_id) or c.open_public = 1
					and s.accepted=0 and s.hidden=0
				where m.content_id=:content_id
				and m.deleted=0";
			if($this->media_id != null){
				$media_sql .= ' and m.id='.$this->media_id;
			}
			$media_sql .= ' group by m.id';
			if($this->sortParams != null){
				$sortArr = json_decode($this->sortParams, true);
				foreach($sortArr as $sortObj){
					$orderBy = $sortObj['property'];
					$key = array_search($orderBy, self::$MEDIA_COLS);
					$orderBy = self::$MEDIA_COLS[$key];
					$direction = strtoupper($sortObj['direction'])==='ASC'?'asc':'desc';
					$media_sql .= ' order by c.'.$orderBy.' '.$direction;
				}
			}
			if($this->start != null && $this->limit != null){
				$media_sql .= ' limit :start, :limit';
			}
			try {
				$results = $this->getDb()->prepare($media_sql);
				$results->bindValue(':content_id', intval(trim($this->content_id)), PDO::PARAM_INT);
				if($this->start != null && $this->limit != null){
					$results->bindValue(':start', intval(trim($this->start)), PDO::PARAM_INT);
					$results->bindValue(':limit', intval(trim($this->limit)), PDO::PARAM_INT);
				}
				$results->execute();
				$total = $results->rowCount();		
				if($total > 0){

					$media = $results->fetchAll(PDO::FETCH_ASSOC);
					$mediaIdx = 0;
					foreach($media as $media_record){
						$media[$mediaIdx]["is_image"] = $this->isImage($media_record["file"]);
						$mediaIdx = $mediaIdx + 1;
					}
					$resCount = $this->getDb()->query('SELECT FOUND_ROWS()');
					$total = (int) $resCount->fetchColumn(); 
					$data = array(
						'results'=>$media,
						'total'=>$total);
					$this->setOutput(self::$SUCCESS, $data);
				}
				else{
					$data = array(
						'media'=>array(),
						'total'=>$total);
					$this->setOutput(self::$SUCCESS, $data);
				}
			} 
			catch(PDOException $ex) {
				$gcotd_msg="An error occurred getting your posts, sorry.";
				$gcotd_msg.="\n<br>An Error occured running the following sql:".$media_sql;
				$gcotd_msg.="\n<br>".$ex->getMessage();
				error_log($gcotd_msg);
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
		}
		else if($this->sortParams != null && 
			$this->start != null && $this->limit != null){
			$this->searchMedia();
		}
		else{
			$gcotd_msg="\nError retrieving media.  No content_id in the request";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
	}
	public function postCaption(){
		// get the media_id
		if(isset($_POST["media_id"]) && $_POST["media_id"] != ""){
			$postMediaId = $_POST["media_id"];
			// get the caption
			$postCaption = addslashes($_POST["caption"]);
			$postCaption = str_replace("<script", "<br>", $postCaption);
			$postCaption = str_replace("<?", "<br>", $postCaption);
			$postCaption = str_replace("\n", "<br>", $postCaption);
			try{
			// construct select sql statement
			$sql = "select m.id, m.content_id, m.id, c.open_public from media m 
				join contents c on c.id = m.content_id and c.user_name=:thisUserName
				where m.id=:postMediaId";
			$stmt = $this->getDb()->prepare($sql);
			$stmt->bindValue(':postMediaId',  intval($postMediaId), PDO::PARAM_INT);
			$stmt->bindValue(':thisUserName',  $this->auth->user_data['name'], PDO::PARAM_STR);
			$stmt->execute();
			if($stmt->rowCount() === 1){				
				$mediaRecord = $stmt->fetch(PDO::FETCH_ASSOC);
				// construct update sql statement
				$sql = "update media set
					title=:postCaption
					where id=:postMediaId";
				$stmt = $this->getDb()->prepare($sql);
				$stmt->bindValue(':postCaption',  $postCaption, PDO::PARAM_STR);
				$stmt->bindValue(':postMediaId',  intval($postMediaId), PDO::PARAM_INT);
				$stmt->execute();
				$content_id = $mediaRecord["content_id"];
				$open_public = $mediaRecord["open_public"];
				$sql = "update contents set
					image_title=:imageTitle
					where id=:content_id";
				$stmt = $this->getDb()->prepare($sql);
				$stmt->bindValue(':imageTitle',  $postCaption, PDO::PARAM_STR);
				$stmt->bindValue(':content_id',  intval($content_id), PDO::PARAM_INT);
				$stmt->execute();
				$gcotd_msg = "Caption successfully posted.";
				$this->setOutput(self::$SUCCESS, $gcotd_msg);
				//file_put_contents($this->get_path()."/debug.log", "Media.php: open_public=$open_public\n", FILE_APPEND);
				if($open_public == 1){
					$this->updateSitemap($content_id, $postMediaId, null);
				}
			}
			else{
				$this->setOutput(self::$FAIL, $postMediaId." does not exist");				
			}
			} catch(PDOException $ex) {
				$gcotd_msg="An error occurred posting your caption, sorry.";
				$gcotd_msg.="\n<br>An Error occured running the following sql:".$sql;
				$gcotd_msg.="\n<br>".$ex->getMessage();
				error_log($gcotd_msg);
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
		}
		else{
			$this->setOutput(self::$FAIL, "No media_id in the request.");				
		}
	}
	public function searchMedia(){
		if($this->sortParams != null && 
			$this->start != null && $this->limit != null){
			$media_sql = "select SQL_CALC_FOUND_ROWS m.id,m.content_id,m.file,m.title,m.num_hits,
				c.user_name,c.create_date_time
				from media m inner join contents c on c.id=m.content_id and m.deleted=0";
			if($this->searchTerm != null){
				$term = htmlentities($this->searchTerm);
				$term = str_replace("&quot;", "\"", str_replace("&quot;", "\"", $term));
				$term = str_replace("&ldquo;", "\"", str_replace("&rdquo;", "\"", $term));
				// tokenize searchTerm into an array while preserving anything in quotes
				if(preg_match_all('/"([^"]+)"/', $term, $quotedStringsArr)){
					foreach($quotedStringsArr[1] as $quotedStr){
						$term = str_replace('"'.$quotedStr.'"', "", $term);
					}
				}
				$searchTermArr = array_merge($quotedStringsArr[1], preg_split("/[\s,]+/", $term, -1, PREG_SPLIT_NO_EMPTY));
				if(count($searchTermArr) > 0){
					//$media_sql .= " and ((c.comment like :searchTerm1 or c.id like :searchTerm2 or m.title like :searchTerm3 or m.file like :searchTerm4)";
					$media_sql .= " and ((m.title like :searchTerm3 or m.file like :searchTerm4)";
					$searchTokenIdx = 0;
					foreach($searchTermArr as $searchToken){
						//$media_sql .= " or (c.comment like :searchTerm1".$searchTokenIdx." or c.id like :searchTerm2".$searchTokenIdx." or m.title like :searchTerm3".$searchTokenIdx." or m.file like :searchTerm4".$searchTokenIdx.")";
						$media_sql .= " or (m.title like :searchTerm3".$searchTokenIdx." or m.file like :searchTerm4".$searchTokenIdx.")";
						$searchTokenIdx = $searchTokenIdx + 1;
					}
					$media_sql .= ")";
				}
				else{
					//$media_sql .= " and (c.comment like :searchTerm1 or c.id like :searchTerm2 or m.title like :searchTerm3 or m.file like :searchTerm4)";
					$media_sql .= " and (m.title like :searchTerm3 or m.file like :searchTerm4)";
				}
			}
			if($this->fromDate != null){
				$media_sql .= " and c.create_date_time >= :fromDate";
			}
			if($this->toDate != null){
				$media_sql .= " and c.create_date_time <= :toDate";
			}
			$media_sql .= " join users u on c.user_name=u.name 
				inner join skillet s on 
					(s.user_id = '".$this->auth->user_data['id']."' and u.id = s.friend_id) or open_public = 1
					and s.accepted=0 and s.hidden=0";		

			if($this->sortParams != null){
				$sortArr = json_decode($this->sortParams, true);
				foreach($sortArr as $sortObj){
					$orderBy = $sortObj['property'];
					$key = array_search($orderBy, self::$MEDIA_COLS);
					$orderBy = self::$MEDIA_COLS[$key];
					$direction = strtoupper($sortObj['direction'])==='ASC'?'asc':'desc';
					$media_sql .= ' order by m.'.$orderBy.' '.$direction;
				}
			}
			if($this->start != null && $this->limit != null){
				$media_sql .= ' limit :start, :limit';
			}
			try {
				$results = $this->getDb()->prepare($media_sql);
				if($this->searchTerm != null){
					if(count($searchTermArr) > 0){
						//$results->bindValue(':searchTerm1', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						//$results->bindValue(':searchTerm2', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$results->bindValue(':searchTerm3', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$results->bindValue(':searchTerm4', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$searchTokenIdx = 0;
						foreach($searchTermArr as $searchToken){
							//$results->bindValue(':searchTerm1'.$searchTokenIdx, "%".trim($searchToken)."%", PDO::PARAM_STR);
							//$results->bindValue(':searchTerm2'.$searchTokenIdx, "%".trim($searchToken)."%", PDO::PARAM_STR);
							$results->bindValue(':searchTerm3'.$searchTokenIdx, "%".trim($searchToken)."%", PDO::PARAM_STR);
							$results->bindValue(':searchTerm4'.$searchTokenIdx, "%".trim($searchToken)."%", PDO::PARAM_STR);
							$searchTokenIdx = $searchTokenIdx + 1;
						}
						$media_sql .= ")";
					}
					else{
						//$results->bindValue(':searchTerm1', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						//$results->bindValue(':searchTerm2', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$results->bindValue(':searchTerm3', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$results->bindValue(':searchTerm4', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
					}

				}
				if($this->fromDate != null){
					$results->bindValue(':fromDate', trim($this->fromDate), PDO::PARAM_STR);
				}
				if($this->toDate != null){
					$results->bindValue(':toDate', trim($this->toDate), PDO::PARAM_STR);
				}
				if($this->start != null && $this->limit != null){
					$results->bindValue(':start', intval(trim($this->start)), PDO::PARAM_INT);
					$results->bindValue(':limit', intval(trim($this->limit)), PDO::PARAM_INT);
				}
				$results->execute();
				$total = $results->rowCount();		
				if($total > 0){

					$media = $results->fetchAll(PDO::FETCH_ASSOC);
					$mediaIdx = 0;
					foreach($media as $media_record){
						$media[$mediaIdx]["is_image"] = $this->isImage($media_record["file"]);
						$mediaIdx = $mediaIdx + 1;
					}
					$resCount = $this->getDb()->query('SELECT FOUND_ROWS()');
					$total = (int) $resCount->fetchColumn(); 
					$data = array(
						'media'=>$media,
						'total'=>$total);
					$this->setOutput(self::$SUCCESS, $data);
				}
				else{
					$data = array(
						'media'=>array(),
						'total'=>$total);
					$this->setOutput(self::$SUCCESS, $data);
				}
			} 
			catch(PDOException $ex) {
				$gcotd_msg="An error occurred getting media, sorry.";
				$gcotd_msg.="\n<br>An Error occured running the following sql:".$media_sql;
				$gcotd_msg.="\n<br>".$ex->getMessage();
				error_log($gcotd_msg);
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
		}
		else{
			$gcotd_msg="\nError searching media.  No sort, start, or limit in the request";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
	}
}
?>