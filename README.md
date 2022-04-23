This is a part of the GRLDCHZ Social network

Copyright (C) 2008 grilledcheeseoftheday.com

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