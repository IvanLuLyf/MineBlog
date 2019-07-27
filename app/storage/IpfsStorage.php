<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/2/27
 * Time: 3:37
 */

namespace MineBlog\Storage;

use BunnyPHP\Storage;

class IpfsStorage implements Storage
{
    private $server;
    private $url;

    public function __construct($config)
    {
        if (isset($config['server']) && $config['server'] != '')
            $this->server = $config['server'];
        else
            $this->server = "https://ipfs.infura.io:5001/api/v0";
        if (isset($config['url']) && $config['url'] != '')
            $this->url = $config['url'];
        else
            $this->url = "https://ipfs.eternum.io/ipfs/";
    }

    public function read($filename)
    {
        return $this->do_get_request($this->server . "/cat?arg=" . $filename);
    }

    public function write($filename, $content)
    {
        define('MULTIPART_BOUNDARY', '--------------------------' . microtime(true));
        $header = 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY;
        $file_contents = $content;
        $content = "--" . MULTIPART_BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename($filename) . "\"\r\n" .
            "Content-Type: application/octet-stream\r\n\r\n" .
            $file_contents . "\r\n";
        $content .= "--" . MULTIPART_BOUNDARY . "--\r\n";
        $context = stream_context_create(['http' => ['method' => 'POST', 'header' => $header, 'content' => $content,], "ssl" => ["verify_peer" => false, "verify_peer_name" => false,],]);
        $result = file_get_contents($this->server . '/add', false, $context);
        $data = json_decode($result, true);
        return "/ipfs/" . $data['Hash'];
    }

    public function upload($filename, $path)
    {
        define('MULTIPART_BOUNDARY', '--------------------------' . microtime(true));
        $header = 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY;
        $file_contents = file_get_contents($path);
        $content = "--" . MULTIPART_BOUNDARY . "\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename($path) . "\"\r\n" .
            "Content-Type: application/octet-stream\r\n\r\n" .
            $file_contents . "\r\n";
        $content .= "--" . MULTIPART_BOUNDARY . "--\r\n";
        $context = stream_context_create(['http' => ['method' => 'POST', 'header' => $header, 'content' => $content,], "ssl" => ["verify_peer" => false, "verify_peer_name" => false,],]);
        $result = file_get_contents($this->server . '/add', false, $context);
        $data = json_decode($result, true);
        return "/ipfs/" . $data['Hash'];
    }

    public function remove($filename)
    {

    }

    private function do_get_request($url)
    {
        $params = ['http' => ['method' => 'GET', 'header' => ['User-Agent: BunnyPHP']]];
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) die();
        $response = @stream_get_contents($fp);
        if ($response === false) die();
        return $response;
    }
}