<?PHP
/**
    GRLDCHZ - a PHP REST-like backing for a social network
	/grldservice/lib/Config.php is part of GRLDCHZ
	
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
class Config{
	private $title;
	private $secure;
	private $domain;
    private $ui_context;
    private $service_context;
    private $path;
    private $media_path;
    private $img_path;
	private $imagemagick;
    private $bin;
    private $ffmpeg;
    private $ffmpeg_args;
    private $rotate_video_cmd;
    private $ffprobe;
    private $qt_faststart;
    private $mysql_url;
    private $mysql_database;
    private $mysql_user;
    private $mysql_password;
    private $admin_id;
    private $admin_user;
    private $admin_email;
    private $css_color;
    private $css_font_color;
    private $css_link_color;
    private $css_menu_color;
    private $css_font_family;
    private $css_banner_img;
    private $css_reply_1_color;
    private $css_reply_2_color;
	private $smtp_host;
	private $smtp_port;
	private $admin_email_password;
	private $captcha_private_key;
	private $cookie_name;
    public function __construct(){
		$env = parse_ini_file(".env");		
	    $this->title              =       $env["title"];//'Example Social Network';
		$this->secure			  =       $env["secure"];//1; // 1 for https
		$this->domain			  =       $env["domain"];//'example.com'; // false for http
		$this->ui_context         =       $env["ui_context"];//'/socialnetwork';
		$this->service_context    =       $env["service_context"];//'/backendservice';
		$this->path          	  =       $env["path"];//'/home/socialnetwork/public_html/backendservice';
		$this->media_path         =       $env["media_path"];//$this->path.'/media';
		$this->img_path           =       $env["img_path"];//$this->path.'/img';
		$this->imagemagick        =       $env["imagemagick"];//'convert'; // path to imagemagick
		$this->bin                =       $env["bin"];//"/usr/local/bin";
		$this->ffmpeg             =       $env["ffmpeg"];//$this->bin."/ffmpeg"; // path to ffmpeg
		$this->ffmpeg_args        =       $env["ffmpeg_args"];//"-acodec libfaac -ab 96k -vcodec libx264 -vpre slower -vpre main -level 21 -refs 2 -b 345k -bt 345k -threads 0 -s 640x360";
		$this->rotate_video_cmd   =       $env["rotate_video_cmd"];//"mencoder -ovc lavc -vf rotate=1 -oac pcm"; // path to mencoder (or ffmpeg)
		$this->ffprobe            =       $env["ffprobe"];//$this->bin."/ffprobe"; // path to ffprobe
		$this->qt_faststart       =       $env["qt_faststart"];//$this->bin."/qt-faststart"; // path to qt-faststart
		$this->mysql_url          =       $env["mysql_url"];//'localhost';
		$this->mysql_database     =       $env["mysql_database"];//'socialnetwork_db';
		$this->mysql_user         =       $env["mysql_user"];//'socialnetwork';
		$this->mysql_password     =       $env["mysql_password"];//'password';
		$this->admin_id           =       $env["admin_id"];//'1';
		$this->admin_user         =       $env["admin_user"];//'admin';
		$this->admin_email        =       $env["admin_email"];//'admin@socialnetwork.com';
		$this->css_color          =       $env["css_color"];//'#A67D3D';
		$this->css_font_color     =       $env["css_font_color"];//'#333333';
		$this->css_link_color     =       $env["css_link_color"];//'#00007f';
		$this->css_menu_color     =       $env["css_menu_color"];//'#FFFFCC';
		$this->css_font_family    =       $env["css_font_family"];//'Arial';
		$this->css_banner_img     =       $env["css_banner_img"];//$this->ui_context.'/img/banner_bg.jpg';
		$this->css_reply_1_color  =       $env["css_reply_1_color"];//'#FFF380';
		$this->css_reply_2_color  =       $env["css_reply_2_color"];//'#FFFFAA';
		$this->smtp_host		  =		  $env["smtp_host"];//'example.com';
		$this->smtp_port		  =		  $env["smtp_port"];//465;
		$this->admin_email_password =     $env["admin_email_password"];//'password';
		$this->captcha_private_key =      $env["captcha_private_key"];//'google-captcha-private-sitekey';
		$this->cookie_name		  =       $env["cookie_name"];//'socialnetwork';
	}
	public function get_title(){ return $this->title; }
    public function get_ui_context(){ return $this->ui_context; }
    public function get_service_context(){ return $this->service_context; }
    public function get_secure(){ return $this->secure; }
    public function get_domain(){ return $this->domain; }
    public function get_path(){ return $this->path; }
    public function get_media_path(){ return $this->media_path; }
    public function get_img_path(){ return $this->img_path; }
    public function get_imagemagick(){ return $this->imagemagick; }
    public function get_bin(){ return $this->bin; }
    public function get_ffmpeg(){ return $this->ffmpeg; }
    public function get_ffmpeg_args(){ return $this->ffmpeg_args; }
    public function get_rotate_video_cmd($input, $output){ 
		// construct a mencoder or a ffmpeg command to rotate a video
		if(stripos($this->rotate_video_cmd, "mencoder") === 0){
			$return_cmd = $this->rotate_video_cmd." ".$input." -o ".$output; 
		}
		else{
			$return_cmd = $this->ffmpeg.' -i '.$input.' '
				.$this->rotate_video_cmd.' '
				.$output; 
		}
		return $return_cmd;
	}
	public function get_ffprobe(){ return $this->ffprobe; }
    public function get_qt_faststart(){ return $this->qt_faststart; }
    public function get_mysql_url(){ return $this->mysql_url; }
    public function get_mysql_database(){ return $this->mysql_database; }
    public function get_mysql_user(){ return $this->mysql_user; }
    public function get_mysql_password(){ return $this->mysql_password; }
    public function get_admin_id(){ return $this->admin_id; }
    public function get_admin_user(){ return $this->admin_user; }
    public function get_admin_email(){ return $this->admin_email; }
    public function get_css_color(){ return $this->css_color; }
    public function get_css_font_color(){ return $this->css_font_color; }
    public function get_css_link_color(){ return $this->css_link_color; }
    public function get_css_menu_color(){ return $this->css_menu_color; }
    public function get_css_font_family(){ return $this->css_font_family; }
    public function get_css_banner_img(){ return $this->css_banner_img; }
    public function get_css_reply_1_color(){ return $this->css_reply_1_color; }
    public function get_css_reply_2_color(){ return $this->css_reply_2_color; }
    public function get_smtp_host(){ return $this->smtp_host; }
    public function get_smtp_port(){ return $this->smtp_port; }
    public function get_admin_email_password(){ return $this->admin_email_password; }
    public function get_captcha_private_key(){ return $this->captcha_private_key; }
	public function get_cookie_name(){ return $this->cookie_name; }
}
?>