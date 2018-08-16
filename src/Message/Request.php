<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 12/08/18
 * Time: 20:12
 */

namespace mbruton\Transport\HTTP\Message;


use mbruton\Transport\HTTP\Address\URL;
use mbruton\Transport\HTTP\Client;

class Request
{
    /**
     * @var string $requestType;
     */
    protected $requestType = Client::REQUEST_GET;

    /**
     * @var Client $httpClient
     */
    protected $httpClient;

    /**
     * @var string|URL $url
     */
    protected $url;

    /**
     * @var Headers $headers;
     */
    protected $headers;

    /**
     * Data to post, put or patch
     * @var string|array $payload
     */
    protected $payload;

    /**
     * The path to the file that should be sent as the
     * payload.
     * @var string $payloadFilename
     */
    protected $payloadFilename;

    /**
     * Output file used to store the response body
     * @var string $outputFilename
     */
    protected $outputFilename;


    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->headers = new Headers();
        $this->httpClient = new Client();
    }

    /**
     * Starts a HTTP Get request
     * @param string|URL $url
     * @return $this
     */
    public function get($url)
    {
        $this->requestType = Client::REQUEST_GET;
        $this->url = $url;
        return $this;
    }

    /**
     * Starts a HTTP Post request
     * @param string|URL $url
     * @return $this
     */
    public function post($url)
    {
        $this->requestType = Client::REQUEST_POST;
        $this->url = $url;
        return $this;
    }

    /**
     * Starts a HTTP Head request
     * @param string|URL $url
     * @return $this
     */
    public function head($url)
    {
        $this->requestType = Client::REQUEST_HEAD;
        $this->url = $url;
        return $this;
    }

    /**
     * Starts a HTTP Patch request
     * @param string|URL $url
     * @return $this
     */
    public function patch($url)
    {
        $this->requestType = Client::REQUEST_PATCH;
        $this->url = $url;
        return $this;
    }

    /**
     * Starts a HTTP Delete request
     * @param string|URL $url
     * @return $this
     */
    public function delete($url)
    {
        $this->requestType = Client::REQUEST_DELETE;
        $this->url = $url;
        return $this;
    }

    /**
     * Starts a HTTP Put request
     * @param string|URL $url
     * @return $this
     */
    public function put($url)
    {
        $this->requestType = Client::REQUEST_PUT;
        $this->url = $url;
        return $this;
    }

    /**
     * Includes the header and value with the request, you
     * can repeat this call as many times are you like.
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function withHeader($key, $value)
    {
        if (isset($key)) {
            $this->headers->addHeader(new Header($key, $value));
        }
        return $this;
    }

    /**
     * Sets the content type of the post, put or patch request,
     * this is the same as ...->withHeader('content-type', '...');
     *
     * @param $contentType
     * @return Request
     */
    public function withContentType($contentType)
    {
        return $this->withHeader('content-type', $contentType);
    }

    /**
     * Includes the data in the post, put or patch request.
     * Use ...->withContentType('...'); to set the media type
     * of this data.  There is no need to set a header with the
     * length as this will be included for you.
     *
     * You can call this method more than once, however, only the
     * last call will succeed.
     *
     * @param $data
     * @return $this
     */
    public function withData($data)
    {
        $this->payload = $data;
        return $this;
    }

    /**
     * When the payload for a put, post or patch request is too big to
     * hold in memory you can use this to specify a filename containing
     * the data.
     *
     * Be sure to set the content type with ->withContentType('...').
     *
     * @param $filename
     * @return $this
     */
    public function withFile($filename)
    {
        if (fileExists($filename)) {
            $this->payloadFilename = $filename;
        }
        return $this;
    }

    /**
     * Use this method to set a file to store the content retrieved from
     * the server.
     *
     * @param string $file
     */
    public function downloadToFile($file)
    {
        $this->outputFilename = $file;
    }

    /**
     * Sends the request to the server and returns the response.
     *
     * @return bool|false|Response
     */
    public function send()
    {
        if (is_null($this->url)) {
            return false;
        }

        if (!is_null($this->payloadFilename)) {
            $this->httpClient->payloadFromFile($this->payloadFilename);
            $this->payload = null; // Kill it if we have it
        }

        if (!is_null($this->outputFilename)) {
            $this->httpClient->outputBodyToFile($this->outputFilename);
        }

        $headers = $this->headers->toArray();
        $response = false;
        switch ($this->requestType) {
            case Client::REQUEST_HEAD:
            case Client::REQUEST_GET:
                $response = $this->httpClient->request($this->url, $this->requestType, $headers);
            case Client::REQUEST_DELETE:
            case Client::REQUEST_PUT:
            case Client::REQUEST_PATCH:
            case Client::REQUEST_POST:
                $response = $this->httpClient->request($this->url, $this->requestType, $headers, $this->payload);
        }

        return $response;
    }
}