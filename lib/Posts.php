<?PHP
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2022 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/Connect.php');
require_once(dirname(__FILE__).'/Skillet.php');
require_once(dirname(__FILE__).'/Utils.php');
class Posts extends Connect{
	private static $CONTENTS_COLS = array(
		'id', 
		'user_name',
		'comment',
		'create_date_time',
		'modify_date_time',
		'parent_id',
		'share_id',
		'image',
		'legacy_id',
		'deleted',
		'num_photos',
		'num_videos',
		'open_public',
		'image_title'
	);	
	private $auth;
	private $content_id;
	private $parent_id;
	private $share_id;
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
				$this->__call('__construct0', $args);
		}
    }
	public function __call($name, $arg){
		return call_user_func_array(array($this, $name), $arg);
	}
	
	public function __construct0(Auth $auth){
		parent::__construct();
		$this->auth = $auth;
		if(isset($_GET["parent_id"]) && $_GET["parent_id"] != null){
			$this->parent_id = $_GET["parent_id"];
		}
		if(isset($_GET["share_id"]) && $_GET["share_id"] != null){
			$this->share_id = $_GET["share_id"];
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
		parent::__construct();
		$this->auth = $auth;
		$this->content_id = $content_id;
		$this->sortParams = '[{"property":"id","direction":"desc"}]';
		$this->start = "0";
		$this->limit = "10";
	}
	public function __construct3(Auth $auth, $start, $limit){
		parent::__construct();
		$this->auth = $auth;
		$this->sortParams = '[{"property":"id","direction":"desc"}]';
		$this->start = $start;
		$this->limit = $limit;
		//file_put_contents($this->get_path()."/debug.log", "\nPosts.php: construct3; start=$this->start; limit=$this->limit; sortParams=$this->sortParams\n", FILE_APPEND);
	}
	public function getPosts(){
		$utils = new Utils($this->auth);
		if($this->sortParams != null && 
			$this->start != null && $this->limit != null){
			//file_put_contents($this->get_path()."/debug.log", "\nGetting posts", FILE_APPEND);
			if($this->parent_id == null){
				$this->parent_id = 0;
			}
			//ob_flush();
			//ob_start();
			//var_dump($this->auth->user_data);
			//file_put_contents($this->get_path()."/debug.log", ob_get_flush(), FILE_APPEND);
			$contents_sql = "select SQL_CALC_FOUND_ROWS 
				c.id, c.user_name, c.comment, c.create_date_time as post_date_time, c.image, c.num_photos, c.num_videos, c.share_id, c.open_public, c.image_title,
				u.first_name, u.img_file, u.last_name, u.create_date_time as user_date_time, u.description
				from contents c
				join users u on c.user_name=u.name
				inner join skillet s on
					(s.user_id = '".$this->auth->user_data['id']."' and u.id = s.friend_id) or c.open_public = 1
					and s.accepted=0 and s.hidden=0
				where deleted = 0 and parent_id = :parent_id";
			if($this->content_id != null && !isset($this->searchTerm) && !isset($this->fromDate) && !isset($this->toDate)){
				$contents_sql .= " and c.id = :content_id";
			}
			if(isset($this->searchTerm)){
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
					$contents_sql .= " and ((c.comment like :searchTerm or c.id like :searchId)";
					$searchTokenIdx = 0;
					foreach($searchTermArr as $searchToken){
						$contents_sql .= " or (c.comment like :searchTerm".$searchTokenIdx." or c.id like :searchId".$searchTokenIdx.")";
						$searchTokenIdx = $searchTokenIdx + 1;
					}
					$contents_sql .= ")";
				}
				else{
					$contents_sql .= " and (c.comment like :searchTerm or c.id like :searchId)";
				}
			}
			if(isset($this->fromDate)){
				$contents_sql .= " and c.create_date_time >= :fromDate";
			}
			if(isset($this->toDate)){
				$contents_sql .= " and c.create_date_time <= :toDate";
			}
			
			$contents_sql .= ' group by c.id';
			
			if($this->sortParams != null){
				$sortArr = json_decode($this->sortParams, true);
				$contents_sql .= ' order by';
				$addComma = false;
				foreach($sortArr as $sortObj){
					$orderBy = $sortObj['property'];
					$key = array_search($orderBy, self::$CONTENTS_COLS);
					$orderBy = self::$CONTENTS_COLS[$key];
					$direction = strtoupper($sortObj['direction'])==='ASC'?'asc':'desc';
					if($addComma){
						$contents_sql .= ',';
					}
					$contents_sql .= ' c.'.$orderBy.' '.$direction;
					$addComma = true;
				}
			}
			if($this->start != null && $this->limit != null){
				$contents_sql .= ' limit :start, :limit';
			}
			try {
				$results = $this->getDb()->prepare($contents_sql);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; contents_sql=$contents_sql\n", FILE_APPEND);
				if($this->content_id != null && !isset($this->searchTerm) && !isset($this->fromDate) && !isset($this->toDate)){
					$results->bindValue(':content_id', intval(trim($this->content_id)), PDO::PARAM_INT);
				}
				if(isset($this->searchTerm)){
					if(count($searchTermArr) > 0){
						$results->bindValue(':searchTerm', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$results->bindValue(':searchId', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$searchTokenIdx = 0;
						foreach($searchTermArr as $searchToken){
							$results->bindValue(':searchTerm'.$searchTokenIdx, "%".trim($searchToken)."%", PDO::PARAM_STR);
							$results->bindValue(':searchId'.$searchTokenIdx, "%".trim($searchToken)."%", PDO::PARAM_STR);
							$searchTokenIdx = $searchTokenIdx + 1;
						}
						$contents_sql .= ")";
					}
					else{
						$results->bindValue(':searchTerm', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
						$results->bindValue(':searchId', "%".trim($this->searchTerm)."%", PDO::PARAM_STR);
					}

				}
				if(isset($this->fromDate)){
					$results->bindValue(':fromDate', trim($this->fromDate), PDO::PARAM_STR);
				}
				if(isset($this->toDate)){
					$results->bindValue(':toDate', trim($this->toDate), PDO::PARAM_STR);
				}
				$results->bindValue(':parent_id', intval(trim($this->parent_id)), PDO::PARAM_INT);
				if($this->start != null && $this->limit != null){
					$results->bindValue(':start', intval(trim($this->start)), PDO::PARAM_INT);
					$results->bindValue(':limit', intval(trim($this->limit)), PDO::PARAM_INT);
				}
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; this->parent_id=$this->parent_id\n", FILE_APPEND);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; this->content_id=$this->content_id\n", FILE_APPEND);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; this->searchTerm=$this->searchTerm\n", FILE_APPEND);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; this->fromDate=$this->fromDate\n", FILE_APPEND);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; this->toDate=$this->toDate\n", FILE_APPEND);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; this->start=$this->start\n", FILE_APPEND);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; this->limit=$this->limit\n", FILE_APPEND);
				$results->execute();
				$resCount = $this->getDb()->query('SELECT FOUND_ROWS()');
				$total = (int) $resCount->fetchColumn(); 
				$postsArr = $results->fetchAll(PDO::FETCH_ASSOC);
				$idx = 0;
				foreach($postsArr as $post){
					$last_initial = substr($post["last_name"],0,1)."."; 
					//$postsArr[$idx]["last_name"] = $last_initial;
					$thumb = str_replace("img_profile", "img_thumb", $post["img_file"]);
					$postsArr[$idx]["img_file"] = $thumb;
					$decodedComment = json_decode($postsArr[$idx]["comment"]);
					if($decodedComment != null){
					$postsArr[$idx]["comment"] = $decodedComment;
					}
					$idx = $idx + 1;
				}
				$postsWithReplies = $this->getPostsWithReplies($postsArr, null);
				$posts = array(
					'results'=>$postsWithReplies,
					'total'=>$total);
				//file_put_contents($this->get_path()."/debug.log", "Posts.php: getPosts; total=$total\n", FILE_APPEND);
				$this->setOutput(self::$SUCCESS, $posts);
			} catch(PDOException $ex) {
				$gcotd_msg="An error occurred getting your posts, sorry.";
				$gcotd_msg.="\n<br>An Error occured running the following sql:".$contents_sql;
				$gcotd_msg.="\n<br>".$ex->getMessage();
				error_log($gcotd_msg);
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
			}
			else{
				$gcotd_msg = "Missing parameters";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}
	}
	private function getPostsWithReplies($posts, $is_share){
		$postIdx = 0;
		foreach($posts as $post){
			$replies_sql = "select SQL_CALC_FOUND_ROWS 
				c.id, c.user_name, c.comment, c.create_date_time as post_date_time, c.image, c.num_photos, c.num_videos,
				c.parent_id, c.share_id, c.open_public,";
			if($post["share_id"] != null){
				$replies_sql .= " (if(c.id=:shared, 1, 0)) as shared,";
				$is_share = 1;
			}
			else if($is_share == 1 || in_array("shared", array_keys($post))){
				$replies_sql .= " 1 as shared,";
				$is_share = 1;
			}
			$replies_sql .= " u.first_name, u.img_file, u.last_name, u.create_date_time as user_date_time, u.description
				from contents c 
				join users u on c.user_name=u.name 
				inner join skillet s on 
					(s.user_id = '".$this->auth->user_data['id']."' and u.id = s.friend_id) or c.open_public = 1
					and s.accepted=0 and s.hidden=0
				where deleted=0";
			if($post["share_id"] != null){
				$replies_sql .= " and (c.parent_id=:parent_id or c.id=:share_id)";
			}
			else{
				$replies_sql .= " and c.parent_id=:parent_id";
			}
			$replies_sql .= " group by c.id";
			$replies_query = $this->getDb()->prepare($replies_sql);
			$replies_query->bindValue(':parent_id', intval(trim($post["id"])), PDO::PARAM_INT);
			if($post["share_id"] != null){
				$replies_query->bindValue(':shared', intval(trim($post["share_id"])), PDO::PARAM_INT);
				$replies_query->bindValue(':share_id', intval(trim($post["share_id"])), PDO::PARAM_INT);
			}
			$replies_query->execute();
			$replies = $replies_query->fetchAll(PDO::FETCH_ASSOC);
			$idx = 0;
			foreach($replies as $reply){
				$last_initial = substr($reply["last_name"],0,1)."."; 
				//$replies[$idx]["last_name"] = $last_initial;
				$thumb = str_replace("img_profile", "img_thumb", $reply["img_file"]);
				$replies[$idx]["img_file"] = $thumb;
				$decodedReply = json_decode($replies[$idx]["comment"]);
				if($decodedReply != null){
					$replies[$idx]["comment"] = $decodedReply;
				}
				$idx = $idx + 1;
			}
			$replyIdx = 0;
			$replies = $this->getPostsWithReplies($replies, $is_share);
			$is_share = 0;
			$posts[$postIdx]["replies"] = $replies;
			$postIdx = $postIdx + 1;
		}
		return $posts;
	}
	public function getSkilletClause(){
		$userName = strtolower($this->auth->user_data["name"]);
		$your_user_id = $this->auth->user_data["id"];
		// get all your comments
		$skilletClause = " and (user_name in ('".$userName."',";
		// get all yer skillet users' comments
		$skillet = new Skillet($this->auth);
		$skillet = $skillet->getSkillet();
		$skilletClause .= "'".implode("','", $skillet)."')";
		$skilletClause .= ")";
		return $skilletClause;
	}
	public function post(){
		if($this->auth->user_data['name'] == 'guest'){
			$this->setOutput(self::$FAIL, "You cannot do anything as Guest.");
		}
		else{
		$postComment = json_encode($_POST["comment"]);
		// get the comment
		//$postComment = addslashes($postComment);
		$postComment = str_replace("<script", "<br>", $postComment);
		$postComment = str_replace("<?", "<br>", $postComment);
		$postComment = str_replace("\n", "<br>", $postComment);
		
		$dateTime = $_POST["dateTime"];
		$formattedDateTime = date(self::$DATE_DISPLAY_FORMAT, strtotime($dateTime));
		
		// get the parentId
		if(isset($_POST["parentId"]) && $_POST["parentId"] !=""){
			$postParentId = $_POST["parentId"];
		}
		else if(isset($_POST["shareId"]) && $_POST["shareId"] !=""){
			$postShareId = $_POST["shareId"];
		}
		else{
			$postParentId = 0;
		}

		$skillet = new Skillet($this->auth);
		$postOpenPublic = 0;
		if(isset($_POST["openPublic"]) && $_POST["openPublic"] == "true"){ 
			if($skillet->checkSkillet(2)){ //user has to be friends with the guest account
				$postOpenPublic = 1;
			}
			else{
				$msg = "You are not authorized to post public content.";
			    $this->setOutput(self::$PERMS, $msg);
				print $this->printOutput();
				exit;
			}				
		}
		try{
			$utils = new Utils($this->auth);
			// get the editId
			if(isset($_POST["editId"]) && $_POST["editId"] != ""){
				$postEditId = $_POST["editId"];
				// construct update sql statement
				$sql = "update contents set
					comment=:postComment,
					user_name=:postUserName, 
					modify_date_time=:dateTime, 
					parent_id=:postParentId,
					open_public=:postOpenPublic
					where id=:postEditId and user_name=:thisUserName";
				$stmt = $this->getDb()->prepare($sql);
				$stmt->bindValue(':postComment',  $postComment, PDO::PARAM_STR);
				$stmt->bindValue(':postUserName',  $this->auth->user_data['name'], PDO::PARAM_STR);
				$stmt->bindValue(':dateTime',  $dateTime, PDO::PARAM_STR);
				$stmt->bindValue(':postParentId',  intval($postParentId), PDO::PARAM_INT);
				$stmt->bindValue(':postEditId',  intval($postEditId), PDO::PARAM_INT);
				$stmt->bindValue(':postOpenPublic',  intval($postOpenPublic), PDO::PARAM_INT);
				$stmt->bindValue(':thisUserName',  $this->auth->user_data['name'], PDO::PARAM_STR);
				$stmt->execute();
				$total = $stmt->rowCount();
				if($total == 0){
					throw new PDOException("Nothing posted");
				}
				else{
					$gcotd_msg = "Comment successfully updated.";
					$this->setOutput(self::$SUCCESS, $gcotd_msg);
					if($postOpenPublic == 1){
						$utils->updateSitemap($postEditId, null, null);
					}
					else{
						$utils->updateSitemap($postEditId, null, true);
					}
				}
			}
			else{

					// construct insert sql statement
				$sql = "INSERT INTO contents (comment, user_name, create_date_time, parent_id, open_public";
				if($postShareId != null){
					$sql .= ", share_id";
				}
				$sql .= ")";
				$sql .= "VALUES (:postComment, :postUserName, :dateTime, :postParentId, :postOpenPublic";
				if($postShareId != null){
					$sql .= ", :postShareId";
				}
				$sql .= ")";

				$stmt = $this->getDb()->prepare($sql);
				$stmt->bindValue(':postComment',  $postComment, PDO::PARAM_STR);
				$stmt->bindValue(':postUserName',  $this->auth->user_data['name'], PDO::PARAM_STR);
				$stmt->bindValue(':dateTime',  $dateTime, PDO::PARAM_STR);
				$stmt->bindValue(':postParentId',  intval($postParentId), PDO::PARAM_INT);
				$stmt->bindValue(':postOpenPublic',  intval($postOpenPublic), PDO::PARAM_INT);
				if($postShareId != null){
					$stmt->bindValue(':postShareId',  intval($postShareId), PDO::PARAM_INT);
				}
				$stmt->execute();
				$gcotd_msg = "Comment successfully posted.";
				$this->setOutput(self::$SUCCESS, $gcotd_msg);
				if($postOpenPublic == 1){
					$utils->updateSitemap($this->getDb()->lastInsertId(), null, null);
				}
			}
			if($postParentId > 0){
				$sql = "update contents set
					modify_date_time=:dateTime 
					where id=:postParentId";
				$stmt = $this->getDb()->prepare($sql);
				$stmt->bindValue(':dateTime',  $dateTime, PDO::PARAM_STR);
				$stmt->bindValue(':postParentId',  intval($postParentId), PDO::PARAM_INT);
				$stmt->execute();
				$total = $stmt->rowCount();
				if($total == 0){
					throw new PDOException("Nothing posted");
				}
			}
		} catch(PDOException $ex) {
			$gcotd_msg="An error occurred posting your comment, sorry.";
			$gcotd_msg.="\n<br>An Error occured running the following sql:".$sql;
			$gcotd_msg.="\n<br>".$ex->getMessage();
			error_log($gcotd_msg);
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
		}
	}
}
?>