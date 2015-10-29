<?php

namespace Forestry\Http;

class Response
{
    const HTTP_OK = 200;
    const HTTP_NOT_FOUND = 404;
    const HTTP_SERVER_ERROR = 500;

    /** @var string */
    private $content;

    /** @var int */
    private $status;

    /**
     * @param string $content
     * @param int $status
     */
    public function __construct($content, $status = self::HTTP_OK)
    {
        $this->content = $content;
        $this->status = $status;
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    private function sendHeaders()
    {
        // TODO: implement appropriate switch/case for status code
    }

    // TODO: implement buffer handling
    private function sendContent()
    {
        echo $this->content;
    }
}
