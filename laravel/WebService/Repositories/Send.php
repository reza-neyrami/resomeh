<?php

namespace App\Repositories\WebService\Repositories;


use App\Repositories\WebService\InterFaces\SendInterFace;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class Send implements SendInterFace
{

    private static $url;
    private static $headers = [];
    public static $options = [];
    public static $groupId;


    /**
     * @param $options
     * @return mixed
     */
    public static function get($options = [])
    {
        return self::send('get', $options);
    }

    /**
     * @param array $options
     * @return mixed
     */
    public static function post($options = [])
    {
        return self::send('post', $options);
    }

    /**
     * @param mixed $url
     */
    public static function setUrl($url): void
    {
        self::$url = $url;
    }

    /**
     * @return mixed
     */
    public static function getUrl()
    {
        return self::$url;
    }

    /**
     * @return mixed
     */
    public static function getHeaders()
    {
        return self::$headers;
    }

    /**
     * @param mixed $headers
     */
    public static function setHeaders($headers): void
    {
        self::$headers = $headers;
    }


    /**
     * @param $method
     * @param $options
     * @return mixed
     */
    private static function send($method, $options = [])
    {
        $requests = new Request($method, self::getUrl(), self::getHeaders());
        $body = (new Client())->sendAsync($requests, $options)->wait();

        return json_decode($body->getBody(), true);
    }
}
