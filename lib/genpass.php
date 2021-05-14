<?PHP
/**
    GRLDCHZ - a PHP REST-like backing for a social network
	/grldservice/lib/genpass.php is part of GRLDCHZ
	
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
require_once(dirname(__FILE__).'/password.php');
$pass=$argv[1];
if(!$pass){
	$pass=getNewPassword();
}
echo $pass;
echo "\n".password_hash($pass, PASSWORD_DEFAULT);
?>