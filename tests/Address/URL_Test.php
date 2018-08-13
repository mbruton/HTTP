<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 12/08/18
 * Time: 22:10
 */

namespace mbruton\Transport\HTTP\Address\Tests;

use mbruton\Transport\HTTP\Address\URL;

use PHPUnit_Extensions_PhptTestCase;


class URL_Test extends PHPUnit_Extensions_PhptTestCase
{
    public function testFromBasicString()
    {
        $url = URL::fromString("http://www.example.com");
        $this->assertEquals('http', $url->getProtocol());
        $this->assertEquals('www.example.com', $url->getHost());
        $this->assertEquals(80, $url->getPort());

        $url = URL::fromString("https://www.domain.wales/some/path/to/file.xml");
        $this->assertEquals('https', $url->getProtocol());
        $this->assertEquals('www.domain.wales', $url->getHost());
        $this->assertEquals(443, $url->getPort());
        $this->assertEquals('/some/path/to/file.xml', $url->getPath());

        $url = URL::fromString("https://www.domain.wales:8989/some/path/to/file.xml");
        $this->assertEquals('https', $url->getProtocol());
        $this->assertEquals('www.domain.wales', $url->getHost());
        $this->assertEquals(8989, $url->getPort());
        $this->assertEquals('/some/path/to/file.xml', $url->getPath());
    }


}
