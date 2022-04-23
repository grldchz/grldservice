<?PHP
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/Connect.php');
require_once(dirname(__FILE__).'/Auth.php');
require_once(dirname(__FILE__).'/Mailer.php');
class Skillet extends Connect{
	private $auth;
	// construct an instance of this class 
	function __construct(Auth $auth){
		parent::__construct();
		$this->auth = $auth;
		if(isset($_POST["start"]) && $_POST["start"] != null){
			$this->start = $_POST["start"];
			$this->limit = $_POST["limit"];
		}
	}
	public function searchSkillets($term, $skilletUserId){
		try{
			$userSql = "select SQL_CALC_FOUND_ROWS";
			if(isset($skilletUserId)){
				$userSql .= " s.id, 
				s.user_id, 
				s.friend_id, 
				s.hidden, 
				s.accepted,";
			}
			$userSql .= " u.id, 
				u.name as user_name, 
				u.first_name, 
				u.img_file, 
				u.last_name, 
				u.create_date_time as user_date_time, 
				u.description,
				(select group_concat(accepted, hidden) from skillet 
					where (user_id = '".$this->auth->user_data["id"]."' and friend_id = u.id)) as outgoing_request,
				(select group_concat(accepted, hidden) from skillet 
					where (user_id = u.id and friend_id  ='".$this->auth->user_data["id"]."')) as incoming_request
				from users u"; 
			if(isset($skilletUserId)){
				$userSql .= " join skillet s on s.user_id = :skillet_user_id 
					or s.friend_id = :skillet_friend_id";
			}
			$userSql .= " where 
					(u.name like :user_name 
					or u.first_name like :first_name 
					or u.last_name like :last_name) 
					and	u.name <> :admin_user
					and u.id <> :user_id 
					and	u.name <> \"guest\"
					and u.id <> \"2\"";
			if(isset($skilletUserId)){
				$userSql .= " and (u.id=s.user_id or u.id=s.friend_id) group by u.id";
			}
			$userSql .= " order by u.first_name asc";
			if($this->start != null && $this->limit != null){
				$userSql .= ' limit :start, :limit';
			}

			$results = $this->getDb()->prepare($userSql);
			$results->bindValue(':user_name', "%".trim($term)."%", PDO::PARAM_STR);
			$results->bindValue(':first_name', "%".trim($term)."%", PDO::PARAM_STR);
			$results->bindValue(':last_name', "%".trim($term)."%", PDO::PARAM_STR);
			$results->bindValue(':admin_user', $this->get_admin_user(), PDO::PARAM_STR);
			$results->bindValue(':user_id', $this->auth->user_data['id'], PDO::PARAM_STR);
			if(isset($skilletUserId)){
				$results->bindValue(':skillet_user_id', $skilletUserId, PDO::PARAM_STR);
				$results->bindValue(':skillet_friend_id', $skilletUserId, PDO::PARAM_STR);
			}
			if($this->start != null && $this->limit != null){
				$results->bindValue(':start', intval(trim($this->start)), PDO::PARAM_INT);
				$results->bindValue(':limit', intval(trim($this->limit)), PDO::PARAM_INT);
			}
			$results->execute();
			$resCount = $this->getDb()->query('SELECT FOUND_ROWS()');
			$total = (int) $resCount->fetchColumn(); 
			$users = $results->fetchAll(PDO::FETCH_ASSOC);
			$idx = 0;
			foreach($users as $user){
				$last_initial = substr($user["last_name"],0,1)."."; 
				$users[$idx]["last_name"] = $last_initial;
				$date_only = substr($user["user_date_time"], 0, 10);
				$users[$idx]["user_date_time"] = $date_only;
				$thumb = str_replace("img_profile", "img_thumb", $user["img_file"]);
				$users[$idx]["img_file"] = $thumb;
				$idx = $idx + 1;
			}
			$retUsers = array(
				'results'=>$users,
				'total'=>$total);
			$this->setOutput(self::$SUCCESS, $retUsers);
		} catch(PDOException $ex) {
			$gcotd_msg="An error occurred searching users, sorry.";
			$gcotd_msg.="\n<br>An Error occured running the following sql:".$userSql;
			$gcotd_msg.="\n<br>".$ex->getMessage();
			error_log($gcotd_msg);
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
	}
	public function getSkillet(){
		$skilletSql = "select 
			s.user_id, s.friend_id, u.id, u.name from skillet s, users u 
			where s.user_id='".$this->auth->user_data['id']."' and hidden='0' and s.friend_id=u.id";
		$skilletResult = $this->getDb()->query($skilletSql);
		return $skilletResult->fetchAll(PDO::FETCH_ASSOC);		
	}
	public function checkSkilletWithUserName($userName, $content_id){
		$userSql = "select u.id from users u 
				inner join contents c on c.id=:content_id and c.user_name = u.name
				inner join skillet s on 
					((s.user_id = '".$this->auth->user_data['id']."' and u.id = s.friend_id)
					or (s.user_id = u.id and s.friend_id = '".$this->auth->user_data['id']."')
					or c.open_public = 1)
					and s.accepted='0'
				";
		
		$userSql .= " where name=:user_name";
		$results = $this->getDb()->prepare($userSql);
		$results->bindValue(':user_name', $userName, PDO::PARAM_STR);
		$results->bindValue(':content_id', intval(trim($content_id)), PDO::PARAM_INT);
		$results->execute();
		if($results->rowCount() > 0){
			return true;
		}
		else{
			return false;
		}			
	}
	public function checkSkillet($userId){
		$skilletSql = "select * from skillet where friend_id='".$userId."' and user_id='".$this->auth->user_data['id']."' and accepted = 0";
		$skilletResult = $this->getDb()->query($skilletSql);
		if($skilletResult->rowCount() > 0){
			return true;
		}
		else{
			return false;
		}			
	}
	public function hideUnhideUser($user){
		if($this->auth->user_data['name'] == 'guest'){
			$this->setOutput(self::$FAIL, "You cannot do anything as Guest.");
		}
		else{
		if(isset($user) && $user != ""){
			$userSql = "update skillet
				set hidden = NOT hidden where
				user_id='".$this->auth->user_data["id"]."' and 
				friend_id=:user";
			$userSqlQuery = $this->getDb()->prepare($userSql);
			$userSqlQuery->bindValue(':user', $user, PDO::PARAM_INT);
			$userSqlQuery->execute();
			$gcotd_msg = "User hidden/unhidden.";
			$this->setOutput(self::$SUCCESS, $gcotd_msg);
		}
		else{
		    $gcotd_msg = "No user in the request.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
		}
	}
	public function removeUser($user){
		if($this->auth->user_data['name'] == 'guest'){
			$this->setOutput(self::$FAIL, "You cannot do anything as Guest.");
		}
		else{
		if(isset($user) && $user != ""){
			//TODO handle where this is admin removing a user from guest.
			$userSql = "delete from skillet where 
				(user_id='".$this->auth->user_data["id"]."' and 
				friend_id=:friend) or (friend_id='".$this->auth->user_data["id"]."' and 
				user_id=:user)";
			$userSqlQuery = $this->getDb()->prepare($userSql);
			$userSqlQuery->bindValue(':friend', $user, PDO::PARAM_INT);
			$userSqlQuery->bindValue(':user', $user, PDO::PARAM_INT);
			$userSqlQuery->execute();
			$gcotd_msg = "User removed.";
			$this->setOutput(self::$SUCCESS, $gcotd_msg);
		}
		else{
		    $gcotd_msg = "No user in the request.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
		}
	}
	public function acceptUser($user){
		if($this->auth->user_data['name'] == 'guest'){
			$this->setOutput(self::$FAIL, "You cannot do anything as Guest.");
		}
		else{
		if(isset($user) && $user != ""){
			//TODO handle where this is admin accepting a user's request to friend guest.
			$updateSkilletSql = "update skillet set accepted=0 
				where user_id=:user
				and friend_id='".$this->auth->user_data["id"]."'";
			$updateSkilletSqlQuery = $this->getDb()->prepare($updateSkilletSql);
			$updateSkilletSqlQuery->bindValue(':user', $user, PDO::PARAM_INT);
			$updateSkilletSqlQuery->execute();
			$addSkilletSql = "insert into skillet (user_id, friend_id, accepted) 
				values ('".$this->auth->user_data["id"]."', :requestUser, 0)";
			$addSkilletSqlQuery = $this->getDb()->prepare($addSkilletSql);
			$addSkilletSqlQuery->bindValue(':requestUser', $user, PDO::PARAM_INT);
			$addSkilletSqlQuery->execute();
			$gcotd_msg = "User accepted.";
			$this->setOutput(self::$SUCCESS, $gcotd_msg);
		}
		else{
		    $gcotd_msg = "No user in the request.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
		}
		}
	}
	public function requestUser($requestUser)
	{
		if($this->auth->user_data['name'] == 'guest'){
			$this->setOutput(self::$FAIL, "You cannot do anything as Guest.");
		}
		else{
		if(isset($requestUser) && $requestUser != null){
			$userFirstName = ucfirst($this->auth->user_data["first_name"]);
			$userLastName = ucfirst($this->auth->user_data["last_name"]);
			$addSkilletSql = "insert into skillet (user_id, friend_id, accepted) 
				values ('".$this->auth->user_data["id"]."', :requestUser, 1)";
			$addSkilletSqlQuery = $this->getDb()->prepare($addSkilletSql);
			$addSkilletSqlQuery->bindValue(':requestUser', $requestUser, PDO::PARAM_INT);
			$addSkilletSqlQuery->execute();
			$selectedUserSql = "select * from users where id=:requestUser";
			$selectedUserQuery = $this->getDb()->prepare($selectedUserSql);
			$selectedUserQuery->bindValue(':requestUser', $requestUser, PDO::PARAM_INT);
			$selectedUserQuery->execute();
			$selectedUserQueryResult = $selectedUserQuery->fetch(PDO::FETCH_ASSOC);
			$selectedFirstName = ucfirst($selectedUserQueryResult["first_name"]);
			$selectedLastName = ucfirst($selectedUserQueryResult["last_name"]);
			$selectedUserEmail = $selectedUserQueryResult["email"];
			$to = $selectedUserEmail;
			$from = "From: " .$this->auth->user_data["email"];
			$subject = $this->get_title()." Friend Request";
			$message = $selectedFirstName.",  ";
			$message .= "do you know ".$userFirstName." ".$userLastName."?  ";
			$message .= $userFirstName." sent you a friend request on ".$this->get_domain().".  ";
			$message .= "To accept ".$userFirstName."'s friend request ";
			$message .= "go to http://".$this->get_domain().$this->get_ui_context().".  ";
			$message .= "Click on the friends button at the top.  ";
			$message .= "Find ".$userFirstName." and click the thumbs up. ";
			$message .= "Refresh your browser.  ";
			$message .= "That's it!  You will now see ".$userFirstName."'s posts and ".$userFirstName." will see yours.";			
			if($this->sendMail($to, $subject, $message)){
				$gcotd_msg = "Request Sent.";
				$this->setOutput(self::$SUCCESS, $gcotd_msg);				
			}
			else{
				$gcotd_msg="For whatever reason I ".
					"failed to send an email with your request.".
					"  Please contact ".$this->get_admin_email().".\r\n".
					"Sorry for the inconvenience.";
				$this->setOutput(self::$FAIL, $gcotd_msg);
			}

		}	
		else{
			$gcotd_msg = "No user in the request.";
			$this->setOutput(self::$FAIL, $gcotd_msg);
			
		}
		}
	}
	private function sendMail($to, $subject, $message){
		$mailer = new Mailer();
		if($mailer->sendmail($to, $subject, $message)){
			return true;
		}
		else{
			error_log("Email sending failed. \r\n".$to."\r\n".$subject."\r\n".$message);
			return false;
		}
	}
}