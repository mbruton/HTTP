<?php
/**
 * Created by PhpStorm.
 * User: mattbruton
 * Date: 11/08/2018
 * Time: 23:55
 */

namespace mbruton\Transport\HTTP\Storage;


interface ICookie
{
    /**
     * Creates a Cookie from a cookie string
     * @param string$cookie
     * @return ICookie
     */
    public static function fromString($cookie);
}