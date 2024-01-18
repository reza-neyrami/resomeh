<?php

namespace App\Repositories\WebService;

use GuzzleHttp\Psr7\Request;

class RequestPsr extends Request
{
    private static $instance;

    public static function getInstance(string $method, $uri, $headers = [], $body = null)
    {
        if (!self::$instance) {
            self::$instance = new self($method, $uri, $headers = [], $body = null);
        }

        return self::$instance;
    }


}
