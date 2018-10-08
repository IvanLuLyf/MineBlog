# MineBlog

本项目为自制的PHP博客系统

## 环境要求

* PHP >= 7.0
* MySQL 或 SQLite

## 安装方式

### 1. 复制本项目

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
|---|---|
|0|ok|
|1001|密码错误|
|1002|用户不存在|
|1003|用户名已存在|
|1004|必要参数为空|
|1005|非法的用户名|
|1006|数据库错误|