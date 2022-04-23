<?PHP
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
class Comment extends Connect{
	private $id;
	private $parentId;
	private $comment;
	private $createDateTime;
	private $image;
	private $userName;
	public function __construct(){
		$num = func_num_args();
		$args = func_get_args();
		switch($num){
			case 1:
				$this->__call('__construct1', $args);
				break;
			default:
				$this->__call('__construct0', $args);
		}
    }
	public function __call($name, $arg){
		return call_user_func_array(array($this, $name), $arg);
	}
	public function __construct0(){
		parent::__construct();
	}
	// overloaded constructor
    public function __construct1($inId){
        parent::__construct();
		$sql = "select * from contents where id=:inId";
		$stmt = $this->getDb()->prepare($sql);
		$stmt->bindValue(':inId',  intval($inId), PDO::PARAM_INT);
		$stmt->execute();
		if($stmt->rowCount() == 1){
			$stmtResult = $stmt->fetch(PDO::FETCH_ASSOC);
			$this->populateThis($stmtResult);
		}
    }
	private function populateThis($row){
		$this->id = $row['id'];
		$this->parentId = $row['parent_id'];
		$this->comment = $row['comment'];
		$this->createDateTime = $row['create_date_time'];
		$this->image = $row['image'];
		$this->userName = $row['user_name'];			
	}
	public function getId(){
		return $this->id;	
	}
	public function getParentId(){
		return $this->parentId;
	}	
	public function getComment(){
		return stripslashes($this->comment);
	}	
	public function getCreateDateTime(){
		return date(self::$DATE_DISPLAY_FORMAT, strtotime($this->createDateTime));
	}	
	public function getImage(){
		return $this->image;
	}	
	public function getUserName(){
		return ucfirst(strtolower($this->userName));
	}	
}
?>