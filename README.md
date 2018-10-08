# MineBlog

This repository is made for my blog site.

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
|1001|password error|
|1002|user not exists|
|1003|username exists|
|1004|empty arguments|
|1005|invalid username|
|1006|database error|