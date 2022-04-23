<?PHP
/**
This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com
**/
require_once(dirname(__FILE__).'/../lib/Auth.php');
require_once(dirname(__FILE__).'/../lib/Forgot.php');
require_once(dirname(__FILE__).'/../lib/Login.php');
require_once(dirname(__FILE__).'/../lib/Register.php');
try{
if(isset($_POST['login'])){
	$login = new Login();
	$login->login();
	print $login->printOutput();
}
else if(isset($_POST['logout'])){
	$login = new Login();
	$login->logout();
	print $login->printOutput();
}
else if(isset($_POST['forgot'])){
	$forgot = new Forgot();
	$forgot->forgot();
	print $forgot->printOutput();
}
else if(isset($_POST['register']))
{
	$register = new Register();
	$register->register();		
	print $register->printOutput();
}
else if(isset($_POST["accept"]))
{
	$auth = new Auth();
	$auth->authenticate();
	print $auth->printOutput();
}
else if(isset($_POST["acceptCookies"]))
{
	$auth = new Auth();
	$auth->authenticate();
	print $auth->printOutput();
}
}
catch(Exception $e){
	$auth = new Auth();
	$auth->setOutput(Auth::$FAIL, $e->getMessage());
	print $auth->printOutput();
}
?>