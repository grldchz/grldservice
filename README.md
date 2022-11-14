# GRLDSERVICE

PHP API for [grldchz](https://github.com/grldchz/grldchz-ts).  GRLDCHZ is a media management system where you can upload images and videos to share with other members of the platform.

Media files are uploaded and stored on the file system (as opposed to the database). 

Image files are processed into lower resolution using imagemagick.

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

## Docker Development

[grldenv](https://github.com/grldchz/grldenv)

## Non-Docker Development

Create a database schema.

Execute grldchz.sql on the MySQL Database

Create lib/.env for your environment.  Use [lib/example.env](lib/example.env) as an example.

Copy the grldservice directory to public_html directory of Apache.

While all calls to grldservice are authenticated with cookies, public posts will be returned without a cookie.

```
https://www.grilledcheeseoftheday.com/grldservice-dev/service.php?get=posts&limit=10&sort=[{"property":"id","direction":"desc"}]&start=0
```
