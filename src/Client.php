<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 11/08/18
 * Time: 13:32
 */

namespace mbruton\Transport\HTTP;

use mbruton\Transport\HTTP\Address\URL;
use mbruton\Transport\HTTP\Message\Cookie;
use mbruton\Transport\HTTP\Message\Header;
use mbruton\Transport\HTTP\Message\Headers;
use mbruton\Transport\HTTP\Message\Response;

class Client
{
    /**
     * Supported request types
     * @var string
     */
    const REQUEST_GET = 'GET';
    const REQUEST_HEAD = 'HEAD';
    const REQUEST_PUT = 'PUT';
    const REQUEST_PATCH = 'PATCH';
    const REQUEST_DELETE = 'DELETE';
    const REQUEST_POST = 'POST';
    const REQUEST_TRACE = 'TRACE';

    protected $supportedRequestTypes = [
        self::REQUEST_GET,
        self::REQUEST_DELETE,
        self::REQUEST_HEAD,
        self::REQUEST_PATCH,
        self::REQUEST_POST,
        self::REQUEST_PUT,
        self::REQUEST_TRACE
    ];

    /**
     * HTTP Status codes: 2XX Success
     * @var int
     */
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;
    const STATUS_NO_CONTENT = 204;
    const STATUS_RESET_CONTENT = 205;
    const STATUS_PARTIAL_CONTENT = 206;
    const STATUS_MULTI_STATUS = 207;
    const STATUS_ALREADY_REPORTED = 208;
    const STATUS_IM_USED = 226;

    /**
     * HTTP Status codes: 3XX Redirection
     * @var int
     */
    const STATUS_MULTIPLE_CHOICES = 300;
    const STATUS_MOVED_PERMANENTLY = 301;
    const STATUS_FOUND = 302;
    const STATUS_SEE_OTHER = 303;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_USE_PROXY = 305;
    const STATUS_SWITCH_PROXY = 306;
    const STATUS_TEMPORARY_REDIRECT = 307;
    const STATUS_PERMANENT_REDIRECT = 308;

    /**
     * HTTP Status codes: 4XX Client error
     * @var int
     */
    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORISED = 401;
    const STATUS_PAYMENT_REQUIRED = 402;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_METHOD_NOT_ALLOWED = 405;
    const STATUS_NOT_ACCEPTABLE = 406;
    const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;
    const STATUS_REQUEST_TIMEOUT = 408;
    const STATUS_CONFLICT = 409;
    const STATUS_GONE = 410;
    const STATUS_LENGTH_REQUIRED = 411;
    const STATUS_PRECONDITION_FAILED = 412;
    const STATUS_PAYLOAD_TOO_LARGE = 413;
    const STATUS_URI_TOO_LONG = 414;
    const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;
    const STATUS_RANGE_NOT_SUPPORTED = 416;
    const STATUS_EXPECTATION_FAILED = 417;
    const STATUS_IM_A_TEAPOT = 418;
    const STATUS_MISDIRECTED_EXPECTED = 419;
    const STATUS_UNPROCESSABLE_ENTITY = 422;
    const STATUS_LOCKED = 423;
    const STATUS_FAILED_DEPENDENCY = 424;
    const STATUS_UPGRADE_REQUIRED = 426;
    const STATUS_PRECONDITION_REQUIRED = 428;
    const STATUS_TOO_MANY_REQUESTS = 429;
    const STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /**
     * HTTP Status codes: 5XX Server error
     * @var int
     */
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED = 501;
    const STATUS_BAD_GATEWAY = 502;
    const STATUS_SERVICES_UNAVAILABLE = 503;
    const STATUS_GATEWAY_TIMEOUT = 504;
    const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    const STATUS_VARIANT_ALSO_NEGOTIATES = 506;
    const STATUS_INSUFFICIENT_STORAGE = 507;
    const STATUS_LOOP_DETECTED = 508;
    const STATUS_NOT_EXTENDED = 510;
    const STATUS_NETWORK_AUTH_REQUIRED = 511;

    /**
     * Maximum number of times to follow redirects for a
     * single request
     */
    const MAX_REDIRECTS = 5;

    /**
     * The default timeout for connections in seconds
     * @var int
     */
    const DEFAULT_TIMEOUT = 30;

    /**
     * Array of socket connections
     * @var array
     */
    protected $connections;

    /**
     * The timeout in seconds for connections to open
     * @var int
     */
    protected $timeout = self::DEFAULT_TIMEOUT;

    /**
     * Cookie jar, yum!
     * @var CookieJar
     */
    protected $cookieJar;

    /**
     * Should we automatically follow redirects?
     * @var bool
     */
    protected $handleRedirects = true;

    /**
     * Base URL
     * @var URL
     */
    protected $baseURL;

    protected $payloadFilename;

    protected $outputFilename;

    /**
     * Constructs a new client
     *
     * @param string $baseURL
     * Optional, when provided requests can be made by giving
     * a relative path instead of the absolute path.
     */
    public function __construct($baseURL = null)
    {
        $this->cookieJar = new CookieJar();
        $this->setBaseURL($baseURL);
    }

    /**
     * Sets the base URL
     *
     * @param string $url
     * The URL to set
     *
     * @return bool
     */
    public function setBaseURL($url)
    {
        if (is_string($url)) {
            $this->baseURL = URL::fromString($url);
            return true;
        }

        return false;
    }

    /**
     * Gets the base URL
     *
     * @return string
     */
    public function getBaseURL()
    {
        if (!is_null($this->baseURL)) {
            return $this->baseURL->getURL(true);
        }

        return null;
    }

    /**
     * Sets the handling of redirects
     *
     * @param bool $handleRedirects
     *
     * @return bool
     */
    public function setHandleRedirects($handleRedirects)
    {
        if (is_bool($handleRedirects)) {
            $this->handleRedirects = $handleRedirects;
            return true;
        }

        return false;
    }

    /**
     * Gets the handling of redirects
     *
     * @return bool
     */
    public function getHandleRedirects()
    {
        return $this->handleRedirects;
    }

    /**
     * Sets the connections being used by this client
     *
     * @param array $connections
     *
     * @return bool
     */
    protected function setConnections($connections)
    {
        if (is_array($connections)) {
            $this->connections = $connections;
            return true;
        }

        return false;
    }

    /**
     * Gets the connections used by this client
     *
     * @return array
     */
    protected function getConnections()
    {
        return $this->connections;
    }

    /**
     * Gets a connection for a specific host and port
     *
     * @param URL $url
     * @return int
     *
     */
    public function getConnection(URL $url)
    {
        return 0;
    }

    public function closeConnection(URL $url)
    {

    }

    /**
     * Set the connection timeout in seconds
     *
     * @param int $seconds
     *
     * @return bool
     */
    public function setTimeout($seconds) {
        if (is_int($seconds) && $seconds > 0) {
            $this->timeout = $seconds;
            return true;
        }

        return false;
    }

    /**
     * Gets the connection timeout in seconds
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Gets the cookie jar
     * @return CookieJar
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    public function payloadFromFile($file)
    {

    }

    public function outputBodyToFile($filename = null) {

    }

    /**
     * Performs the request HTTP call
     *
     * @param string|URL $path
     *
     * @param string $requestType
     *
     * @param string|string[] $headers
     *
     * @param mixed $data
     *
     * @param int $redirectCount
     *
     * @return Response|false
     */
    public function request($path = null, $requestType = self::REQUEST_GET, $requestHeaders = [], $data = null, $shouldFollowRedirects = true, $redirectCount = 0)
    {
        /** Check the request type is valid */
        $requestType = trim(strtoupper($requestType));
        if (!in_array($requestType, $this->supportedRequestTypes)) {
            return false;
        }

        /** Normalise the URL */
        $requestURL = $path;
        if (is_string($requestURL)) {
            $requestURL = URL::fromString($requestURL);
        }

        if ($this->baseURL instanceof URL) {
            $requestURL = $this->baseURL->getFinalURL($path);
        }

        /** Get a connection to the host */
        $socket = $this->getConnection($requestURL);

        if (!$socket) {
            return false;
        }

        /** Build a ResponseObject */
        $response = new Response();

        /** Write the protocol header */
        $dataPacket = sprintf("%s %s HTTP/1.1\r\n", $requestType, $requestURL->getAbsolutePath(true));

        /** Write the headers */
        $originalHeaders = $requestHeaders;
        $requestHeaders = array_merge(['Host' => $requestURL->getHost()], $requestHeaders);
        $payloadSize = 0;
        if (!is_null($data) && is_string($data)) {
            $payloadSize = strlen($data);
        }
        $dataPacket .= $this->buildRequestHeaders($requestHeaders, $payloadSize);

        /** Write the cookies */
        $dataPacket .= $this->cookieJar->getCookiesForURLAsString($requestURL);

        /** Send the packet down the socket */
        fwrite($socket, $dataPacket, strlen($dataPacket));

        /** Send the data or stream the file */
        if (!is_null($data)) {
            fwrite($socket, $data, strlen($data));
        } elseif (!is_null($this->payloadFilename)) {
            $this->streamFileFromSocket($socket, $this->payloadFilename);
        }

        /** Read the first line of the response */
        $responseStatus = fgets($socket);

        /** Break the status into parts */
        list($protocolVersion, $statusCode, $statusMessage) = explode("=", $responseStatus);

        /** Check the protocol version is known */
        if (!in_array(strtoupper($protocolVersion), ['HTTP/1.0', 'HTTP/1.1'])) {
            return false;
        }

        /** Set the response http version */
        $response->setHTTPVersion($protocolVersion);

        /** Set the status code and message */
        $response->setStatusCode(intval($statusCode));
        $response->setStatusMessage($statusMessage);

        /**
         * We need to stream the headers from the socket until
         * we receive a carriage return followed by a line feed.
         */
        $headers = new Headers();
        while("\r\n" != ($data = fgets($socket))) {
            $headers->addHeader($data);
        }

        /** Add the headers to the response */
        $response->setHeaders($headers);

        /** Add any cookies from the headers to the cookie jar */
        $this->cookieJar->addCookiesFromHeaders($headers);

        /**
         * HTTP servers return data in one of two ways, they
         * either return a header 'Content-Length' which tells
         * us it's safe to pull the entire lot from the stream,
         * or 'Transfer-Encoding' will be set to 'chunked' and
         * we will need to pull the length of the chunk and then
         * the chunk itself, and then repeat until we get a chunk
         * with a length of 0.
         */

        /** Check if we have a content length */
        $contentLength = $headers->getValueForHeader('content-length');

        if (is_null($contentLength)) {
            /** Check if transfer encoding is chunked */
            if (strtolower($headers->getHeadersForKey('transfer-encoding')) != 'chunked') {
                /** Don't know what to do :/ */
                return false;
            }
        }

        /**
         * Check if we are adding the data to the response
         * or storing it in a file
         */
        if (!is_null($this->outputFilename)) {
            /** Add to the response */
            $responseBody = $this->streamDataFromSocket($socket, $contentLength);
            $response->setBody($responseBody);
        } else {
            /** Write to file */
            $this->streamFileFromSocket($socket, $this->outputFilename, $contentLength);
            $response->setBodyFilename($this->outputFilename);
        }

        /** Check if we are required to close the connection */
        if (strtolower($headers->getValueForHeader('connection')) == 'close') {
            $this->closeConnection($requestURL);
        }

        /**
         * We may have received a status code to redirect, we could
         * have acted on this before getting the response body, however
         * that would have left the stream open with data which is
         * a little rude.
         */

        /** Check if we are required to redirect and allowed to redirect */
        if ($shouldFollowRedirects) {

            /** Prevent redirect loops */
            if ($redirectCount >= self::MAX_REDIRECTS) {
                /** Unable to follow, return where we are at */
                return $response;
            }

            $redirectStatusCodes = [
                self::STATUS_MOVED_PERMANENTLY,
                self::STATUS_FOUND,
                self::STATUS_SEE_OTHER,
                self::STATUS_TEMPORARY_REDIRECT,
                self::STATUS_PERMANENT_REDIRECT
            ];

            if (in_array($response->getStatusCode(), $redirectStatusCodes)) {
                $redirectURL = URL::fromString($headers->getValueForHeader('location'));
                /**
                 * The URL could be a full URL or a path so we need to merge it with the original
                 */
                $redirectURL = $requestURL->getFinalURL($redirectURL);

                /**
                 * If we have a 303 (See Other) we need to redirect as a
                 * 'GET' without a payload, else we need to do a full
                 * direct
                 */
                if ($response->getStatusCode() == self::STATUS_SEE_OTHER) {
                    return $this->request($redirectURL);
                } else {
                    return $this->request($redirectURL, $requestType, $originalHeaders, $data, $shouldFollowRedirects, $redirectCount++);
                }
            }
        }

        /** Return the response */
        return $response;
    }


    public function streamFileToSocket($socket, $filename)
    {

    }

    public function streamFileFromSocket($socket, $filename, $length = null)
    {

    }

    public function streamDataFromSocket($socket, $length = null)
    {

    }

    public function sentToSocket($socket, $data)
    {

    }

    public function getFromSocket($socket, $length = null)
    {

    }

    public function buildRequestHeaders(array $headers, $payloadSize = null)
    {
        $output = '';

        /** @todo Include Accept-Encoding */
        /** @todo If payloadSize null, check if theres an payloadFile */

        return $output;
    }



    /**
     * Makes a HTTP Get request
     *
     * @param string $path
     * A path relative to 'baseURL' or an absolute URL
     *
     * @param string[] $headers
     *
     * @return Response
     */
    public function get($path, $headers = [])
    {

    }



}