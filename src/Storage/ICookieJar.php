<?php
/**
 * Created by PhpStorm.
 * User: mattbruton
 * Date: 11/08/2018
 * Time: 23:56
 */

namespace mbruton\Transport\HTTP\Storage;


use mbruton\Transport\HTTP\Address\URL;

interface ICookieJar
{

    /**
     * Adds a cookie to the jar
     * @param ICookie|string $cookie
     */
    public function addCookie($cookie);

    /**
     * Gets the cookies for a URL
     * @param URL|string $url
     * @return Cookie[]
     */
    public function getCookiesForURL($url);

    /**
     * Gets all cookies
     * @return ICookie[]
     */
    public function getAllCookies();

    /**
     * Gets all cookies for a URL as a string headers
     * @param URL|string $url
     */
    public function getCookiesAsHeadersForURL($url);
}