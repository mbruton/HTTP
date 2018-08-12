<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 12/08/18
 * Time: 17:47
 */

namespace mbruton\Transport\HTTP\Message;


class Header
{

    protected $key;

    protected $value;

    public function __construct($key = null, $value = null)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * Creates and returns a new header object from the given
     * string.
     *
     * @param string $stringHeader
     * @return Header
     */
    public static function fromString($stringHeader)
    {
        list($key, $value) = explode(':', $stringHeader);
        $header = new self(trim($key), trim($value));

        return $header;
    }
}