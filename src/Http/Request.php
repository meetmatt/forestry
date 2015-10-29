<?php

namespace Forestry\Http;

use Forestry\ParameterBag\ImmutableParameterBag;

class Request
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    /**
     * Request path
     * @var string
     */
    private $path;

    /**
     * HTTP method
     * @var string
     */
    private $method;

    /**
     * SERVER vars which are prefixed with HTTP_
     * @var ImmutableParameterBag
     */
    public $headers;

    /**
     * GET variables
     * @var ImmutableParameterBag
     */
    public $query;

    /**
     * POST variables
     * @var ImmutableParameterBag
     */
    public $request;

    /**
     * No magic or cookies
     *
     * @param string $path
     * @param string $method
     * @param array $headers
     * @param array $query
     * @param array $request
     */
    public function __construct($path, $method, array $headers = [], array $query = [], array $request = [])
    {
        $this->path = $path;
        $this->method = $method;
        $this->headers = new ImmutableParameterBag($headers);
        $this->query = new ImmutableParameterBag($query);
        $this->request = new ImmutableParameterBag($request);
    }

    /**
     * Read PHP
     * @return self
     */
    public static function buildFromGlobals()
    {
        $path = $_SERVER['PATH_INFO'];
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $headers = getallheaders();
        $get = $_GET;
        $post = $_POST;

        return new self($path, $method, $headers, $get, $post);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
