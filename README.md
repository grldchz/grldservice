# grldservice
PHP REST-like service for backing grldchz social network.  grldchz is a social networking platform where you can post text, photos, and videos.

REST-like in that all calls to it respond with JSON.

Photos and videos are processed into lower resolution, space saving web format, that allows for faster load times over the internet.

Assumptions: 
<br>grldservice is installed on a LAMP or WAMP system
<br>ImageMagick, Mencoder, and FFMPEG are installed on target system.  Adjustments would have to be made to the ffmpeg_args variable in lib/Config.php to work with target system FFMPEG version.

Install Bitnami WAMP (easiest way to get started)

The following php.ini entries are optional:
<blockquote>
<br>date.timezone='US/Eastern'
<br>log_errors=On
<br>error_log='/&lt;root&gt;/public_html/errors.log'
<br>memory_limit = 256M
<br>upload_max_filesize = 256M
<br>post_max_size =  256M
<br>max_execution_time = 200
</blockquote>

see <a href="https://github.com/grldchz/grldchz">grldchz</a> for info on the UI

<p>Steps to Install:</p>
Either create database grldchz manually or uncomment create database lines in grldchz.sql.
Execute grldchz.sql on the MySQL Database
<br>Copy the grldservice directory to public_html directory of Apache
<br>