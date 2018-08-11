<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/8/4
 * Time: 22:12
 */


require_once APP_PATH . "library/AliOSS/autoload.php";

use OSS\OssClient;
use OSS\Core\OssException;

class AliStorage implements Storage
{
    protected $ossClient;
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $endpoint;
    protected $bucket;
    protected $url;

    public function __construct($config)
    {
        $this->accessKeyId = $config['key'];
        $this->accessKeySecret = $config['secret'];
        $this->endpoint = $config['endpoint'];
        $this->bucket = $config['bucket'];
        $this->url = $config['url'];

        try {
            $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
        } catch (\OSS\Core\OssException $e) {
            exit($e->getMessage());
        }
    }

    public function read($filename)
    {
        $content = $this->ossClient->getObject($this->bucket, $filename);
        return $content;
    }

    public function write($filename, $content)
    {
        $this->ossClient->putObject($this->bucket, $filename, $content);
    }

    public function upload($filename, $path)
    {
        try {
            $this->ossClient->uploadFile($this->bucket, $filename, $path);
        } catch (OssException $e) {
            exit($e->getMessage());
        }
    }

    public function remove($filename)
    {
        $this->ossClient->deleteObject($this->bucket, $filename);
    }

    public function geturl($filename)
    {
        return $this->url . $filename;
    }
}