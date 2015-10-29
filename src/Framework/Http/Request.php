<?php

namespace Forestry\Framework\Http;

use Forestry\Framework\ParameterBag\ImmutableParameterBag;

class Request
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const ROOT = '/';

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
        $path = self::getPathFromGlobals();
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $headers = getallheaders();
        $get = $_GET;
        $post = $_POST;

        return new self($path, $method, $headers, $get, $post);
    }

    /**
     * Gets request path from URI
     *
     * @return string
     */
    private static function getPathFromGlobals()
    {
        $path = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'];

        // strip query string from uri
        if ($pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }

        // strip script name from uri
        if (false !== ($pos = strpos($path, $scriptName))) {
            $path = substr($path, $pos + strlen($scriptName), strlen($path));
        }

        // no path = root
        if (strlen($path) === 0) {
            return self::ROOT;
        }

        return $path;
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
