<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 11/08/18
 * Time: 13:32
 */

namespace mbruton\Transport\HTTP;

use mbruton\Transport\HTTP\Address\Path;
use mbruton\Transport\HTTP\Address\URL;
use mbruton\Transport\HTTP\Message\Cookie;
use mbruton\Transport\HTTP\Message\Header;
use mbruton\Transport\HTTP\Message\Headers;
use mbruton\Transport\HTTP\Message\Response;
use mbruton\Transport\HTTP\Message\Status;

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
     * an absolute path instead of the absolute URL.
     * Relative paths are currently not supported
     */
    public function __construct($baseURL = null)
    {
        $this->cookieJar = new CookieJar();
        $this->setBaseURL($baseURL);
    }

    /**
     * Destructs this class back to the letters and symbols
     * from which it arose but not before severing ties
     * with all the http servers it networked with. Bless.
     */
    public function __destruct()
    {
        /** Close all open connections */
        foreach($this->connections as $key => $handle) {
            /** The big K'O */
            fclose($handle);
        }
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
        $connectionKey = sprintf("%s:%s:%s", $url->getProtocol(), $url->getHost(), $url->getPort());

        /** Check if we are already connected */
        if (isset($this->connections[$connectionKey])) {
            return $this->connections[$connectionKey];
        }

        /** Establish a new connection */
        list($errorNumber, $errorString) = null;
        $connectionHandle = fsockopen(
            $url->getHost(),
            $url->getPort(),
            $errorNumber,
            $errorString,
            $this->timeout
        );

        /** Check if the connection opened successfully */
        if (!$connectionHandle) {
            /** We can't continue so we are going to return false */
            return false;
        }

        /** Add to our connections array */
        $this->connections[$connectionKey] = $connectionHandle;

        /**
         * If we are using SSL then we will need enable encryption or fail if
         * we are unable to
         */
        if (strtolower($url->getProtocol()) == 'https') {
            /** Attempt to enable encryption using the best available method */
            if (
                false == stream_socket_enable_crypto(
                    $connectionHandle,
                    true,
                    STREAM_CRYPTO_METHOD_ANY_CLIENT
                )
            ){
                /** It just didn't work out, close the connection */
                $this->closeConnection($url);
                return false;
            }
        }

        /** Return the socket handle */
        return $connectionHandle;
    }

    /**
     * Once upon a time it was mandatory for HTTP clients to
     * disconnect after the request was completed, however,
     * some bright boffin out there on the tinterwebs somewhere
     * realised it was more efficient to keep the connection open
     * as the browser would probably want to get pictures and
     * scripty stuff.  And so it is. (I'm assuming thats what happpened
     * I haven't actually checked, but it makes for good reading all the
     * same).
     *
     * Unless the server server sends back the header 'Connection: close'
     * we are just going to keep it open until this class destructs,
     * so this method hopefully will not be called all that often.
     *
     * @param URL $url
     */
    public function closeConnection(URL $url)
    {
        $connectionKey = sprintf("%s:%s:%s", $url->getProtocol(), $url->getHost(), $url->getPort());

        /** Check if we have a connection */
        if (isset($this->connections[$connectionKey])) {
            /** Close and unset */
            fclose($this->connections[$connectionKey]);
            unset($this->connections[$connectionKey]);
        }
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

    /**
     * Set the name of the file where the payload to be
     * sent from the server should be sent from.
     * Returns true if the filename was accepted (meaning
     * it really is genuine real file), or false if it
     * wasn't.
     *
     * @param string $file
     * @return bool
     */
    public function payloadFromFile($file)
    {
        if (file_exists($file)) {
            $this->payloadFilename = $file;
            return true;
        }

        return false;
    }

    /**
     * Set the filename where the response data should be stored.
     * This is useful when you need to download large content that
     * otherwise wouldn't be viable to return in the response.
     *
     * Leaving the file name results in a filename generated at random
     * in the temp directory, this name is returned.
     *
     *
     * @param string|null $filename
     * The filename where the output should be stored
     *
     * @return string
     * The filename to
     */
    public function outputBodyToFile($filename = null) {
        if (is_null($filename)) {
            $filename = sys_get_temp_dir();
            $filename .= md5(date('Ymdhis') . rand(0, 65536));
        }

        $this->outputFilename = $filename;
        return $filename;
    }


    protected function initialiseConnection(URL $url, $requestType = self::REQUEST_GET)
    {
        /** Open a connection to the URL and check it connected */
        $connectionHandle = $this->getConnection($url);

        if ($connectionHandle === false) {
            /** Failed to connect */
            return false;
        }

        /** Check if the request type is valid */
        $requestType = trim(strtoupper($requestType));
        if (!in_array($requestType, $this->supportedRequestTypes)) {
            /** Unsupported request type, close the connection */
            $this->closeConnection($url);
            return false;
        }

        /**
         * We need to tell the http server the type of request we are
         * sending, get, post, etc, and also the path of the resource
         * we wish to engage in followed by the protocol version.
         *
         * GET /some/file.php HTTP/1.1
         */
        $requestData = sprintf("%s %s HTTP/1.1\r\n", $requestType, $url->getAbsolutePath(true));

        /** Send the data */
        fwrite($connectionHandle, $requestData, strlen($requestData));

        /** Return the handle */
        return $connectionHandle;
    }

    protected function sendHeaders($connectionHandle, URL $url, Headers $headers, $payloadSize = null)
    {
        $shouldExpectContinue = is_null($payloadSize) ? false : true;

        if (!is_object($headers) && !$headers instanceof Headers) {
            $this->closeConnection($url);
            return false;
        }

        $headers->addHeader(new Header('Host', $url->getHost()));

        /** Check if we support compression */
        $supportedCompressionMethods = [];

        if (function_exists(gzdeflate())) {
            $supportedCompressionMethods[] = 'deflate';
        }

        if (function_exists(gzdecode())) {
            $supportedCompressionMethods[] = 'decode';
        }

        if (count($supportedCompressionMethods)) {
            $headers->addHeader(new Header('Accept-Encoding', implode(', ', $supportedCompressionMethods)));
        }

        if (is_int($payloadSize) && $payloadSize > 0) {
            $headers->addHeader('Content-Length', $payloadSize);
        }

        /** Add cookies */
        $cookieHeaders = $this->cookieJar->getCookiesAsHeadersForURL($url);

        foreach($cookieHeaders as $cookieHeader) {
            $headers->addHeader($cookieHeader);
        }

        /**
         * If we have a payload we are going to send a header named
         * 'Expect' with the value of 100, which is the status we expect
         * the server to return.
         *
         * We do this because the payload could potentially be huge and
         * bandwidth / resource hungry and be a complete waste of time
         * if it turns out to be a 404 or something.
         *
         * So 'Expect' instructs the http server to check the URL and
         * headers and if its satisfied that the request appears valid
         * it returns a status '100: Continue', instructing us to
         * send the payload.
         */
        if ($shouldExpectContinue) {
            $headers->addHeader(new Header('Expect', Status::STATUS_CONTINUE));
        }

        $requestData = $headers->toString();

        /** Send the data */
        fwrite($connectionHandle, $requestData, strlen($requestData));


        /** Create a response object */
        $response = new Response();

        /**
         * Default the status code to 100: Continue
         * We do this so that after we return, the caller (->request(..)
         * probably) can just look for a 100 to know if it's safe to
         * continue even if we didn't send 'Expect: 100'.
         */
        $response->getStatusCode(Status::STATUS_CONTINUE);

        /** If we sent Expect: 100 header we need to check the status */
        if ($shouldExpectContinue) {
            $status = fgets($connectionHandle);
            list($version, $status, $message) = explode(" ", $status, 3);
            $response->setHTTPVersion($version);
            $response->setStatusCode((int)$status);
            $response->setStatusMessage($message);

            /** If we haven't received a status 100 then somethings not quite right */
            if ($response->getStatusCode() != Status::STATUS_CONTINUE) {
                $this->closeConnection($url);
            }
        }

        return $response;
    }


    public function readRequestStatus($connectionHandle, &$response)
    {
        $status = fgets($connectionHandle);
        list($version, $status, $message) = explode(" ", $status, 3);
        $response->setHTTPVersion($version);
        $response->setStatusCode((int)$status);
        $response->setStatusMessage($message);
    }

    public function sendPayload($connectionHandle, $data = null)
    {
        if (!is_null($data)) {
            /** Send the data from the incoming param */
            fwrite($connectionHandle, $data, strlen($data));
        } elseif (!is_null($this->payloadFilename) && file_exists($this->payloadFilename)) {
            /** Send the data from a file */
            $fileHandle = fopen($this->payloadFilename, 'rb');

            if ($fileHandle === false) {
                return false;
            }

            while(!feof($fileHandle)) {
                $fileData = fread($fileHandle, 8192);
                fwrite($connectionHandle, $fileData, strlen($fileData));
            }

            fclose($fileHandle);
        }

        return true;
    }

    public function readHeaders($connectionHandle, &$response)
    {
        $headers = new Headers();

        while("\r\n" != ($data = fgets($connectionHandle))){
            $header = Header::fromString($data);
            $headers->add($header);
        }

        $response->setHeaders($headers);
    }

    protected function handleRedirect(Response &$response, URL $url, $requestType, Headers $requestHeaders, $data = null, $redirectCount = 0)
    {
        /** Check we haven't exceeded the maximum no of redirects */
        if ($redirectCount >= self::MAX_REDIRECTS) {
            /** Too many hops */
            return false;
        }

        $location = null;

        switch($response->getStatusCode()) {
            case Status::STATUS_SEE_OTHER: /** 303 */
                /**
                 * This status indicates that we've posted data
                 * and the http server has redirected us after the
                 * post to a new URL that we should 'GET' without
                 * posting anymore data.
                 *
                 * Intentionally there is no 'break' as we still need
                 * to collect the new location.
                 */

                /** Become a GET request */
                $requestType = self::REQUEST_GET;

                /**
                 * Remove any data we have, this includes removing
                 * the payload filename if there is one.
                 */
                $data = null;
                $this->payloadFilename = null;

                /**
                 * @todo Strip out headers pertaining to the
                 *       the now non-existent payload, such as
                 *       content-* (length, type, etc)
                 */


            case Status::STATUS_MULTIPLE_CHOICES: /** 300 */
            case Status::STATUS_MOVED_PERMANENTLY: /** 301 */
            case Status::STATUS_FOUND: /** 302 */
            case Status::STATUS_NOT_MODIFIED: /** 304 */
            case Status::STATUS_USE_PROXY: /** 305 */
            case Status::STATUS_SWITCH_PROXY: /** 306 */
            case Status::STATUS_TEMPORARY_REDIRECT: /** 307 */
            case Status::STATUS_PERMANENT_REDIRECT: /** 308 */
                $location = $response->getHeaders()->getHeadersForKey('location');
                break;
        }

        if (!is_string($location) || $location == '') {
            /** No where to go :/ */
            return false;
        }

        /** Check the URL is valid, if not check if it's a path */
        if (URL::isURL($location)) {
            /**
             * It's a URL, so we will just use that
             */
            $url = $location;
        } elseif (Path::isPath($location)) {
            $url->setPath($location);
        }

        /** Redirect */
        return $this->request(
            $url,
            $requestType,
            $requestHeaders,
            null,
            true,
            $redirectCount++
        );
    }

    public function readBody($connectionHandle, Response &$response)
    {
        /**
         * The HTTP server will transmit the body in one of two ways.
         * 1. There will be a header 'content-length' that will allow
         *    us to read that amount of data from the stream.
         * 2. The header 'transfer-encoding' will be set to 'chunked',
         *    for this we will need to read off each chunk one by one,
         *    each one starts with the length on a line of it's own
         *    followed by the chunk.  The length is raw bytes so
         *    we will have to make it more useful.
         */
        $contentLength = (int)$response->getHeaders()->getHeadersForKey('content-length');
        if (!is_null($contentLength) && $contentLength > 0) {
            /** Are we reading to memory or a file? */
            if (!is_null($this->outputFilename)) {
                /** Output to file */
                $fileHandle = fopen($this->outputFilename, 'wb');
                if (!$fileHandle) {
                    return false;
                }

                /** Stream direct to disk */
                fwrite($fileHandle, fread($connectionHandle, $contentLength), $contentLength);

                /** Close the file handle */
                fclose($fileHandle);

                /** Set the response and clear the filename */
                $response->setBodyFilename($this->outputFilename);
                $this->outputFilename = null;

                return true;

            } else {
                /** Output to the response object */
                $response->setBody(fread($connectionHandle, $contentLength));

                return true;
            }
        }

        /** If we got here then we may have a chunked response */
        $transferEncoding = $response->getHeaders()->getHeaderForKey('transfer-encoding');
        if (is_null($transferEncoding)) {
            /** Don't know whats going on anymore, has the world gone mad? */
            return false;
        }

        /**
         * Are we streaming to file or the response object?
         * TBH the fact that it's chunked is probably a good indication of it's
         * size, and so we should be streaming to a file, but the user knows
         * best and it's not like PHP will abruptly die if it runs out of
         * memory...
         */
        if (!is_null($this->outputFilename)) {
            /** Stream to file */
            $fileHandle = fopen($this->outputFilename, 'wb');

            if (!$fileHandle) {
                return false;
            }

            while("\r\n" != ($data = fgets($connectionHandle))) {
                /** Data should contain the chunk size, we just need to make it more useful */
                $chunkLength = hexdec($data);

                /** Check we have a number above 0 */
                if (is_int($chunkLength) && $chunkLength > 0) {
                    /** Write the chunk directly to disk */
                    fwrite($fileHandle, fread($connectionHandle, $chunkLength), $chunkLength);
                }
            }

            /**
             * Remove the final \r\n else if we make a second request, the output
 *           * will start with it and nothing will work right.
             */
            fread($connectionHandle, 2);

            /** Close the file handle and return */
            fclose($fileHandle);
            return true;
        }

        /**
         * Oh no :/
         * If PHP was ever going to run aground, it's in these next
         * few lines of code.  We could be trying to put more data
         * than we have memory, into memory, the problem is, we
         * just don't know until we've already done it.
         */
        $body = '';
        while("\r\n" != ($data = fgets($connectionHandle))) {
            $chunkLength = hexdec($data);

            /** Check we have a number above 0 */
            if (is_int($chunkLength) && $chunkLength > 0) {
                /** Write the chunk to memory */
                $body .= fread($connectionHandle, $chunkLength);
            }
        }

        /** Phew, that was lucky */
        $response->setBody($body);

        return true;
    }

    public function decompressBody(&$response)
    {
        $contentEncoding = strtolower($response->getHeaders()->getHeaderForKey('content-encoding'));
        if (!in_array($contentEncoding, ['gzip', 'deflate'])) {
            return true;
        }

        // @todo ...
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

        /** Normalise the URL */
        $requestURL = $path;
        if (is_string($requestURL)) {
            $requestURL = URL::fromString($requestURL);
        }

        if ($this->baseURL instanceof URL) {
            $requestURL = $this->baseURL->getFinalURL($path);
        }

        /** Get a connection to the server */
        $connectionHandle = $this->initialiseConnection($requestURL, $requestType);

        if ($connectionHandle === false) {
            /** Failed to connect or initialise */
            return false;
        }

        /** Convert the headers */
        $headersObject = new Headers();
        if (is_array($requestHeaders)) {
            foreach($requestHeaders as $header => $value) {
                $headersObject->addHeader(new Header($header, $value));
            }
        }

        /** Send the headers */
        $payloadSize = null;
        if (!is_null($data)) {
            $payloadSize = strlen($data);
        } elseif (!is_null($this->payloadFilename) && file_exists($this->payloadFilename)) {
            $payloadSize = filesize($this->payloadFilename);
        }
        $response = $this->sendHeaders($connectionHandle, $requestURL, $requestHeaders, $payloadSize);
        if ($response === false) {
            return false;
        }

        /** Check we are ok to continue */
        if ($response->getStatusCode() != Status::STATUS_CONTINUE) {
            // @todo Check for redirects
            return $response;
        }

        /**
         * If we have a payload we can send it now
         */
        if (!$this->sendPayload($connectionHandle, $data)) {
            return false;
        }

        /** Read the status code */
        $this->readRequestStatus($connectionHandle, $response);

        /** Read the headers */
        $this->readHeaders($connectionHandle, $response);

        /**
         * There's a good chance that the http server has been
         * friendly and given us some cookies.  Now, don't want to
         * spoil out tea, so lets pop them in the jar and eat them
         * later. Yum!
         */
        $this->cookieJar->addCookiesFromHeaders($requestURL, $response->getHeaders());

        /** Check for redirects */
        if (Status::getStatusClass($response->getStatusCode()) == Status::CLASS_3XX_REDIRECTION) {
            /** Redirect or fail */
            return $this->handleRedirect($response, $requestURL, $requestType, $headersObject, $data, $redirectCount);
        }

        /** Read the body */
        if (!$this->readBody($connectionHandle, $response)) {
            return false;
        }

        /** Decompress the body as needed */


//        /** Get a connection to the host */
//        $socket = $this->getConnection($requestURL);
//
//        if (!$socket) {
//            return false;
//        }

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
                Status::STATUS_MOVED_PERMANENTLY,
                Status::STATUS_FOUND,
                Status::STATUS_SEE_OTHER,
                Status::STATUS_TEMPORARY_REDIRECT,
                Status::STATUS_PERMANENT_REDIRECT
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
                if ($response->getStatusCode() == Status::STATUS_SEE_OTHER) {
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