<?PHP
/**
	This is a part of the grilledcheeseoftheday.com

	Copyright (C) 2022 grilledcheeseoftheday.com

    GRLDCHZ is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    GRLDCHZ is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see http://www.gnu.org/licenses/.
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