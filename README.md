# MineBlog

![MineBlog](static/img/mineblog.png?raw=true)

MineBlog is a simple blog software powered by BunnyPHP framework.

![GitHub release](https://img.shields.io/github/release/ivanlulyf/mineblog.svg?color=brightgreen)
![GitHub](https://img.shields.io/github/license/ivanlulyf/mineblog.svg?color=blue)

## Requirement

* PHP >= 7.0
* MySQL or SQLite

## Installation

### 1. Clone this repository to your site root.

### 2. Set up your server
> Apache

Add following content to ```.htacess``` file.

```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php
</IfModule>
```


> Nginx

```
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

### 3. Open http://yourdomain/install

## Error Code

|Code|Description|
|---|---|
|0|ok|
|-1|network error|
|-2|mod does not exist|
|-3|action does not exist|
|-4|template does not exist|
|-5|template rendering error|
|-6|database error|
|-7|parameter cannot be empty|
|-8|internal error|
|1|invalid csrf token|
|2|invalid file|
|1001|wrong password|
|1002|user does not exist|
|1003|username already exists|
|1004|invalid username|
|1005|registration is not allowed|
|1006|invalid id code|
|1007|oauth is not enabled|
|2001|invalid client id|
|2002|permission denied|
|2003|invalid token|
|2004|invalid oauth code|
|2005|invalid refresh token|
|3001|invalid tid|
|3002|permission denied|