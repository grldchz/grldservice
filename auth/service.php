<?PHP
/**
    GRLDCHZ - a PHP REST-like backing for a social network
	/grldservice/auth/service.php is part of GRLDCHZ
	
    Copyright (C) 2021 grilledcheeseoftheday.com

    GRLDCHZ is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    GRLDCHZ is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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