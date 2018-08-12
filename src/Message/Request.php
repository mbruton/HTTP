<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 12/08/18
 * Time: 20:12
 */

namespace mbruton\Transport\HTTP\Message;


class Request
{

    public function get($url)
    {

        return $this;
    }

    public function post($url)
    {
        return $this;
    }

    public function head($url)
    {

    }

    public function patch($url)
    {

    }

    public function delete($url)
    {

    }

    public function put($url)
    {

    }

    public function withHeader($key, $value)
    {
        return $this;
    }

    public function withContentType($contentType)
    {
        return $this->withHeader('content-type', $contentType);
    }

    public function withData($data)
    {
        return $this;
    }

    public function withFile($data)
    {
        return $this;
    }

    public function downloadToFile($file)
    {

    }

    public function send()
    {

    }



}