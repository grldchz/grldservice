<?PHP
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/password.php');
$pass=$argv[1];
if(!$pass){
	$pass=getNewPassword();
}
echo $pass;
echo "\n".password_hash($pass, PASSWORD_DEFAULT);
?>