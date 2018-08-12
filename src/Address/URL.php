<?php

namespace mbruton\Transport\HTTP\Address;


class URL
{
    /**
     * Supported protocols
     * @var string[]
     */
    protected $supportedProtocols = ['http', 'https'];

    /**
     * Holds the current protocol
     * @var string
     */
    protected $protocol = 'http';

    /**
     * Holds the username
     * @var string
     */
    protected $username;

    /**
     * Holds the password
     */
    protected $password;

    /**
     * Hostname
     * @var string
     */
    protected $host;

    /**
     * Port
     * @var int
     */
    protected $port = 80;

    /**
     * Path
     * @var string
     */
    protected $path;

    /**
     * Query string
     * @var string
     */
    protected $queryString;

    /**
     * Params
     * @var string[]
     */
    protected $params = [];


    public function __construct()
    {

    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function setProtocol($protocol)
    {
        $protocol = trim(strtolower($protocol));
        if (!in_array($this->supportedProtocols, $protocol)) {
            return false;
        }

        $this->protocol = $protocol;
        return true;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        if (!is_int($port) && $port <= 0 && $port >= 65536) {
            // throw new ExceptionInvalidPort();
            return false;
        }

        $this->port = $port;
        return true;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getQueryString()
    {
        $queryString = '';
        foreach($this->params as $key => $value) {
            if ($queryString != '') {
                $queryString .= '&';
            }
            $queryString .= urlencode($key);
            $queryString .= "=";
            $queryString .= urlencode($value);
        }
    }

    public function setQueryString($queryString)
    {
        if (!is_string($queryString)) {
            return;
        }

        $pairs = explode("&", $queryString);

        $this->params = [];

        if (!count($pairs)) {
            return;
        }

        foreach($pairs as $pair) {
            list($key, $value) = explode("=", $pair);
            $this->params[urldecode($key)] = urldecode($value);
        }
    }

    public function getParam($key)
    {
        if (isset($this->params[$key])){
            return $this->params[$key];
        }

        return null;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParam($key, $value)
    {
        $key = urldecode($key);
        $value = urldecode($value);

        $this->params[$key] = $value;
    }

    public function getURL($includeQueryString = true)
    {
        $url = $this->protocol . "://";
        if (!is_null($this->username) && $this->username != '') {
            $url .= $this->username;
            if (!is_null($this->password)) {
                $url .= ":" . $this->password;
            }

            $url .= "@";
        }

        $url .= $this->host;

        if (!is_null($this->port)) {
            if (
                ($this->protocol == 'http' && $this->port != 80) ||
                ($this->protocol == 'https' && $this->port != 443) ||
                ($this->protocol != 'http' && $this->protocol != 'https')
            ) {
                $url .= ':' . $this->port;
            }
        }

        $url .= $this->path;

        if ($includeQueryString) {
            $queryString = $this->getQueryString();
            if (!is_null($queryString) && $queryString != '') {
                $url .= '?' . $queryString;
            }
        }

        return $url;
    }

    public function getAbsolutePath($includeQueryString = true)
    {
        $url = $this->path;

        if ($includeQueryString) {
            $queryString = $this->getQueryString();
            if (!is_null($queryString) && $queryString != '') {
                $url .= '?' . $queryString;
            }
        }

        return $url;
    }

    public function __toString()
    {
        return $this->getURL();
    }

    /**
     * Creates a new URL object from a string
     *
     * @param string $url
     * The URL to be parsed
     *
     * @return string
     *
     * @throws ExceptionInvalidPort
     */
    public static function fromString($url)
    {
        if (!is_string($url)) {
            return new URL();
        }

        if (self::isURL($url)) {
            return self::fromURL($url);
        }

        if (self::isPath($url)) {
            return self::fromPath();
        }
    }

    public static function isURL($url)
    {

    }

    public static function isPath($path)
    {

    }

    public function fromURL($url)
    {
        $output = new URL();
        $output->setProtocol('http');
        $output->setPort(80);
        $output->setPath('/');

        if (strpos($url, "?") !== false){
            list($url, $query_string) = explode("?", $url, 2);

            $output['query_string'] = $query_string;

            if (isset($query_string) && $query_string != ""){
                $pairs = explode("&", $query_string);
                foreach($pairs as $pair){
                    list($key, $value) = explode("=", $pair);
                    $output->setParam($key, $value);
                }
            }
        }

        $pattern = "/^(([A-Za-z]+):\/\/)?(([-_A-Za-z0-9]+)(:([^@]+))?@)?([^\/:]*)(:([0-9]+))?([^?]*)/";
        $matches = array();

        if (preg_match($pattern, $url, $matches)){
            if (isset($matches[2]) && $matches[2] != ""){
                $output->setProtocol(strtolower($matches[2]));
            }

            if (isset($matches[4]) && $matches[4] != ""){
                $output->setUsername($matches[4]);
            }

            if (isset($matches[6]) && $matches[6] != ""){
                $output->setPassword($matches[6]);
            }

            if (isset($matches[7]) && $matches[7] != ""){
                $output->setHost($matches[7]);
            }

            if (isset($matches[9]) && $matches[9] != ""){
                try{
                    $output->setPort(intval($matches[9]));
                } catch(ExceptionInvalidPort $e) {

                }
            }else{
                if ($output->getProtocol() == 'https') {
                    $output->setPort(443);
                }
            }

            if (isset($matches[10]) && $matches[10] != ""){
                $output->setPath($matches[10]);
            }
        }

        return $output;
    }

    public function fromPath($path)
    {

    }

    /**
     * From the given value a URL is made using the value
     * and the baseURL value
     * @param $urlOrPath
     */
    public function getFinalURL($urlOrPath)
    {

    }



}