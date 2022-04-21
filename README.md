GRLDCHZ Social network

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
along with this program.  If not, see <http://www.gnu.org/licenses/>.

# GRLDSERVICE

PHP REST-like service for backing [grldchz](https://github.com/grldchz/grldchz-ts)  social network.  grldchz is a social networking platform where you can post text, photos, and videos.

REST-like in that all calls to it respond with JSON.

Photos are processed into lower resolution, space saving web format, that allows for faster load times over the internet.

## Run-time Dependencies

* apache
* imagemagick
* mysql
* php
* php extension pdo
* php extension pdo_mysql
* phpmailer
* sendmail

## Get Started

Either create database grldchz manually or uncomment create database lines in grldchz.sql.

Execute grldchz.sql on the MySQL Database

Create lib/.env for your environment.  See lib/example.env for more info.

Copy the grldservice directory to public_html directory of Apache