# MineBlog

![MineBlog](static/img/mineblog.png?raw=true)

MineBlog is a simple blog software powered by BunnyPHP framework.

[![Release](https://img.shields.io/github/release/ivanlulyf/mineblog.svg?color=brightgreen&style=flat-square)](https://packagist.org/packages/ivanlulyf/mineblog)
[![Packagist](https://img.shields.io/packagist/dt/ivanlulyf/mineblog.svg?color=lightgreen&style=flat-square)](https://packagist.org/packages/ivanlulyf/mineblog)
![Code Size](https://img.shields.io/github/languages/code-size/ivanlulyf/mineblog.svg?color=orange&style=flat-square)
![License](https://img.shields.io/github/license/ivanlulyf/mineblog.svg?color=blue&style=flat-square)

![PHP](https://img.shields.io/badge/PHP->%3D7.0.0-777bb3.svg?style=flat-square&logo=php)
[![BunnyPHP](https://img.shields.io/packagist/v/ivanlulyf/bunnyphp.svg?color=pink&label=%F0%9F%90%B0BunnyPHP&style=flat-square)](https://github.com/IvanLuLyf/BunnyPHP)

English | [中文](README_CN.md)

## Requirement

* PHP >= 7.0
* MySQL or SQLite

## Installation

### 1. Download program

> Through git 

Clone this repository to your site root.And run composer install

```shell
git clone https://github.com/IvanLuLyf/MineBlog.git
cd MineBlog
composer install
```

> Through composer

```shell
composer create-project ivanlulyf/mineblog MineBlog
```

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

## Error code

|Code|Description|
|:---:|---|
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