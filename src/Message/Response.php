<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 11/08/18
 * Time: 15:12
 */

namespace mbruton\Transport\HTTP\Message;


class Response
{
    /**
     * Holds the server's HTTP version string
     * @var string
     */
    protected $httpVersion;

    /**
     * Holds the HTTP status code returned by the server
     * @var int
     */
    protected $statusCode;

    /**
     * Holds the status message returned by the server
     * @var string
     */
    protected $statusMessage;

    /**
     * Holds the headers returned by the server
     * @var Header[]
     */
    protected $headers;

    /**
     * Holds the body of the response
     * @var string
     */
    protected $body;

    /**
     * Holds the file name where the body of the response can be
     * found.
     * @var string
     */
    protected $bodyFilename;

    public function __construct()
    {
    }

    public function setHTTPVersion($version)
    {
        $this->httpVersion = $version;
    }

    public function getHTTPVersion()
    {
        return $this->httpVersion;
    }

    public function setStatusCode($statusCode)
    {
        if (is_int($statusCode)) {
            $this->statusCode = $statusCode;
        }
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusMessage($statusMessage)
    {
        $this->statusMessage = $statusMessage;
    }

    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBodyFilename($filename)
    {
        $this->bodyFilename = $filename;
    }

    public function getBodyFilename($filename)
    {
        return $this->bodyFilename;
    }
}