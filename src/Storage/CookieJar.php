<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 11/08/18
 * Time: 16:17
 */

namespace mbruton\Transport\HTTP;


use mbruton\Transport\HTTP\Address\URL;
use mbruton\Transport\HTTP\Storage\ICookieJar;

class CookieJar implements ICookieJar
{

    public function addCookie($cookie)
    {
        // TODO: Implement addCookie() method.
    }

    public function getAllCookies()
    {
        // TODO: Implement getAllCookies() method.
    }

    public function getCookiesForURL($url)
    {
        $url = $this->normalizeURL($url);

        if ($url === false) {
            return false;
        }



        // TODO: Implement getCookiesForURL() method.
    }

    public function getCookiesAsHeadersForURL($url)
    {
        $url = $this->normalizeURL($url);

        if ($url === false) {
            return false;
        }
        // TODO: Implement getCookiesAsHeadersForURL() method.
    }

    private function normalizeURL($url) {
        if (is_string($url)) {
            $url = URL::fromString($url);
        }

        if (!is_object($url) && !$url instanceof URL) {
            return false;
        }

        return $url;
    }
}