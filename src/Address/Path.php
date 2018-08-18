<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 16/08/18
 * Time: 17:59
 */

namespace mbruton\Transport\HTTP\Address;


class Path
{
    public static function isPath($path)
    {
        if (is_object($path) && $path instanceof self) {
            return true;
        }

        if (!is_string($path)) {
            return false;
        }
        $pattern = "/(\/?([-._~!$&'()*+,;=:@A-Z0-9a-z]|%[0-9]{2,2})*)*/";
        return preg_match($pattern, $path) ? true : false;
    }

    public static function isRelativePath($path)
    {
        if (!self::isPath($path)) {
            return false;
        }

        if (substr($path, 0, 1) == '/') {
            return false;
        }

        return true;
    }

    public static function isAbsolutePath($path)
    {
        if (!self::isPath($path)) {
            return false;
        }

        if (substr($path, 0, 1) == '/') {
            return true;
        }

        return false;
    }

    public function fromString($string)
    {
        $foo = (true == false) ? 'boo' : 'bar';
    }
}