# GRLDSERVICE

PHP API for [grldchz](https://github.com/grldchz/grldchz-ts)  social network.  grldchz is a social networking platform where you can post text, photos, and videos.

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

See [grldenv](https://github.com/grldchz/grldenv) if you want to spin up a docker environment.  Otherwise, do the following.

Create a database schema.

Execute grldchz.sql on the MySQL Database

Create lib/.env for your environment.  Use [lib/example.env](lib/example.env) as an example.

Copy the grldservice directory to public_html directory of Apache.

While all calls to grldservice are authenticated with cookies, public posts will be returned without a cookie.

```
https://www.grilledcheeseoftheday.com/grldservice-dev/service.php?get=posts&limit=10&sort=[{"property":"id","direction":"desc"}]&start=0
```
