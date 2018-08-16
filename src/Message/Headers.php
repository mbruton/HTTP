<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 12/08/18
 * Time: 17:52
 */

namespace mbruton\Transport\HTTP\Message;


class Headers
{
    /**
     * @var Header[]
     */
    protected $headers;

    public function addHeader($header)
    {
        if (is_string($header)) {
            $header = Header::fromString($header);
        }

        if (is_object($header) && $header instanceof Header) {
            $this->headers[] = $header;
        }
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeadersForKey($key)
    {
        $key = trim(strtolower($key));
        $output = [];
        foreach($this->headers as $header) {
            if ($key == strtolower($header->getKey())) {
                $output = $header;
            }
        }

        return $output;
    }

    public function getValueForHeader($key)
    {

    }

    public function toArray()
    {

    }

    public function toString()
    {
        return "";
    }

    public function __toString()
    {
        return $this->toString();
    }
}