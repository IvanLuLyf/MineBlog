# MineBlog

![MineBlog](static/img/mineblog.png?raw=true)

MineBlog是基于BunnyPHP的简易PHP博客系统

[![Release](https://img.shields.io/github/release/ivanlulyf/mineblog.svg?color=brightgreen&style=flat-square)](https://packagist.org/packages/ivanlulyf/mineblog)
[![Packagist](https://img.shields.io/packagist/dt/ivanlulyf/mineblog.svg?color=lightgreen&style=flat-square)](https://packagist.org/packages/ivanlulyf/mineblog)
![Code Size](https://img.shields.io/github/languages/code-size/ivanlulyf/mineblog.svg?color=orange&style=flat-square)
![License](https://img.shields.io/github/license/ivanlulyf/mineblog.svg?color=blue&style=flat-square)

![PHP](https://img.shields.io/badge/PHP->%3D7.0.0-777bb3.svg?style=flat-square&logo=php)
[![BunnyPHP](https://img.shields.io/packagist/v/ivanlulyf/bunnyphp.svg?color=pink&label=%F0%9F%90%B0BunnyPHP&style=flat-square)](https://github.com/IvanLuLyf/BunnyPHP)

[English](README.md) | 中文

## 环境要求

* PHP >= 7.0
* MySQL 或 SQLite

## 安装方式

### 1. 下载程序

> 通过git 

复制本项目,运行Composer安装脚本

```shell
git clone https://github.com/IvanLuLyf/MineBlog.git
cd MineBlog
composer install
```

> 通过composer

```shell
composer create-project ivanlulyf/mineblog MineBlog
```

### 2. 配置服务器环境
> Apache

添加以下内容到 ```.htacess``` 文件中

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

### 3. 打开 http://yourdomain/install 完成安装

## 错误码

|状态值|描述|
|:---:|---|
|0|ok|
|-1|网络错误|
|-2|Mod不存在|
|-3|Action不存在|
|-4|模板不存在|
|-5|模板渲染错误|
|-6|数据库错误|
|-7|参数不可为空|
|-8|内部错误|
|1|非法的csrf token|
|2|无效的文件|
|1001|密码错误|
|1002|用户不存在|
|1003|用户名已存在|
|1004|无效的用户名|
|1005|不允许注册|
|1007|oauth未启用|
|2001|无效的client id|
|2002|没有权限|
|2003|无效的token|
|2004|无效的oauth code|
|2005|无效的refresh token|
|3001|无效的tid|
|3002|没有权限|