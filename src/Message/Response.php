<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 11/08/18
 * Time: 15:12
 */

namespace mbruton\Transport\HTTP\Message;


class Response
{
    protected $httpVersion;
    protected $statuCode;
    protected $statusMessage;
    protected $headers;
    protected $body;
}