<?php

namespace Forestry\Framework\Http;

class Response
{
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_NOT_FOUND = 404;
    const HTTP_SERVER_ERROR = 500;

    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';

    /** @var string */
    private $content;
    /** @var int */
    private $status;
    /** @var string */
    private $contentType;

    /**
     * @param string $content
     * @param int $status
     * @param string $contentType
     */
    public function __construct($content, $status = self::HTTP_OK, $contentType = self::CONTENT_TYPE_TEXT_HTML)
    {
        $this->content = $content;
        $this->status = $status;
        $this->contentType = $contentType;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    private function sendHeaders()
    {
        switch ($this->status) {
            case self::HTTP_NOT_FOUND:
                header("HTTP/1.1 404 Not Found");
                break;
            case self::HTTP_SERVER_ERROR:
                header("HTTP/1.1 500 Internal Server Error");
                break;
            default:
                header("HTTP/1.1 200 Found");
        }

        header('Content-Type: ' . $this->contentType);
    }

    // TODO: implement buffer handling
    private function sendContent()
    {
        if ($this->status !== self::HTTP_NO_CONTENT) {
            echo $this->content;
        }
    }
}
