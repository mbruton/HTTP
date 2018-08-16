<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 13/08/18
 * Time: 20:36
 */

namespace mbruton\Transport\HTTP\Message;


class Status
{
    /**
     * HTTP Status classification
     * @var int
     */
    const CLASS_1XX_INFORMATIVE = 1;
    const CLASS_2XX_SUCCESS = 2;
    const CLASS_3XX_REDIRECTION = 3;
    const CLASS_4XX_CLIENT_ERROR = 4;
    const CLASS_5XX_SERVER_ERROR = 5;

    /**
     * HTTP Status codes: 1XX Informational
     * @var int
     */
    const STATUS_CONTINUE = 100;
    const STATUS_SWITCHING_PROTOCOLS = 101;
    const STATUS_PROCESSING = 102;
    const STATUS_EARLY_HINTS = 103;

    /**
     * HTTP Status codes: 2XX Success
     * @var int
     */
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;
    const STATUS_NO_CONTENT = 204;
    const STATUS_RESET_CONTENT = 205;
    const STATUS_PARTIAL_CONTENT = 206;
    const STATUS_MULTI_STATUS = 207;
    const STATUS_ALREADY_REPORTED = 208;
    const STATUS_IM_USED = 226;

    /**
     * HTTP Status codes: 3XX Redirection
     * @var int
     */
    const STATUS_MULTIPLE_CHOICES = 300;
    const STATUS_MOVED_PERMANENTLY = 301;
    const STATUS_FOUND = 302;
    const STATUS_SEE_OTHER = 303;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_USE_PROXY = 305;
    const STATUS_SWITCH_PROXY = 306;
    const STATUS_TEMPORARY_REDIRECT = 307;
    const STATUS_PERMANENT_REDIRECT = 308;

    /**
     * HTTP Status codes: 4XX Client error
     * @var int
     */
    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORISED = 401;
    const STATUS_PAYMENT_REQUIRED = 402;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_METHOD_NOT_ALLOWED = 405;
    const STATUS_NOT_ACCEPTABLE = 406;
    const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;
    const STATUS_REQUEST_TIMEOUT = 408;
    const STATUS_CONFLICT = 409;
    const STATUS_GONE = 410;
    const STATUS_LENGTH_REQUIRED = 411;
    const STATUS_PRECONDITION_FAILED = 412;
    const STATUS_PAYLOAD_TOO_LARGE = 413;
    const STATUS_URI_TOO_LONG = 414;
    const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;
    const STATUS_RANGE_NOT_SUPPORTED = 416;
    const STATUS_EXPECTATION_FAILED = 417;
    const STATUS_IM_A_TEAPOT = 418;
    const STATUS_MISDIRECTED_EXPECTED = 419;
    const STATUS_UNPROCESSABLE_ENTITY = 422;
    const STATUS_LOCKED = 423;
    const STATUS_FAILED_DEPENDENCY = 424;
    const STATUS_UPGRADE_REQUIRED = 426;
    const STATUS_PRECONDITION_REQUIRED = 428;
    const STATUS_TOO_MANY_REQUESTS = 429;
    const STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /**
     * HTTP Status codes: 5XX Server error
     * @var int
     */
    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED = 501;
    const STATUS_BAD_GATEWAY = 502;
    const STATUS_SERVICES_UNAVAILABLE = 503;
    const STATUS_GATEWAY_TIMEOUT = 504;
    const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    const STATUS_VARIANT_ALSO_NEGOTIATES = 506;
    const STATUS_INSUFFICIENT_STORAGE = 507;
    const STATUS_LOOP_DETECTED = 508;
    const STATUS_NOT_EXTENDED = 510;
    const STATUS_NETWORK_AUTH_REQUIRED = 511;

    public static function getStatusClass($status)
    {
        if (!is_int($status)) {
            return false;
        }

        if ($status >= 100 && $status < 200) {
            return self::CLASS_1XX_INFORMATIVE;
        } elseif ($status >= 200 && $status < 300) {
            return self::CLASS_2XX_SUCCESS;
        } elseif ($status >= 300 && $status < 400) {
            return self::CLASS_3XX_REDIRECTION;
        } elseif ($status >= 400 && $status < 500) {
            return self::CLASS_4XX_CLIENT_ERROR;
        } elseif ($status >= 500 && $status < 600) {
            return self::CLASS_5XX_SERVER_ERROR;
        }

        return false;
    }
}