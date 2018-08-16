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
    /**
     * Header name
     * @var string $key
     */
    protected $key;

    /**
     * Header value
     * @var string $value
     */
    protected $value;

    /**
     * Header constructor.
     *
     * @param string|null $key
     * @param string|null $value
     */
    public function __construct($key = null, $value = null)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Sets the value of the header
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Sets the value of the header
     *
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the header name
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Gets the header name
     *
     * @return null|string
     */
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