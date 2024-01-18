<?php

namespace App\Repositories\WebService;

use GuzzleHttp\Client;

class WebService
{

    private static $url;
    private static $headers = [];
    public static $options = [];

    public function __construct()
    {

    }

    public static function send($method, $options = [])
    {

        $client = new Client();
        $requests = RequestPsr::getInstance($method, self::getUrl(), self::getHeaders());
        $body = $client->sendAsync($requests, $options)->wait();

        return json_decode($body->getBody(), true);
    }


    /**
     * @return mixed
     */
    public static function getUrl()
    {
        return self::$url;
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
    public static function getHeaders()
    {
        return self::$headers;
    }

    /**
     * @param mixed $headers
     */
    public static function setHeaders($headers = []): void
    {

        self::$headers = array_merge($headers);
    }

    public static function withToken($token, $type = 'Bearer')
    {

        return self::$options['headers']['Authorization'] = trim($type . ' ' . $token);


    }
}
