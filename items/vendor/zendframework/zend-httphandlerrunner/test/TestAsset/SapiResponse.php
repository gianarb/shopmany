<?php
/**
 * Zend Framework (https://framework.zend.com/)
 *
 * This file exists to allow overriding the various output-related functions
 * in order to test what happens during the `Response\SapiEmitter::emit()` cycle.
 *
 * These functions include:
 *
 * - headers_sent(): we want to always return false so that headers will be
 *   emitted, and we can test to see their values.
 * - header(): we want to aggregate calls to this function.
 *
 * It pushes headers into the HeaderStack class defined in HeaderStack.php.
 *
 * @see       https://github.com/zendframework/zend-serverhandler-runnder for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-serverhandler-runnder/blob/master/LICENSE.md New BSD License
 */

namespace Zend\HttpHandlerRunner\Emitter;

use ZendTest\HttpHandlerRunner\TestAsset\HeaderStack;

/**
 * Have headers been sent?
 *
 * @return false
 */
function headers_sent()
{
    return false;
}

/**
 * Emit a header, without creating actual output artifacts
 *
 * @param string   $string
 * @param bool     $replace
 * @param int|null $http_response_code
 */
function header($string, $replace = true, $http_response_code = null)
{
    HeaderStack::push(
        [
            'header'      => $string,
            'replace'     => $replace,
            'status_code' => $http_response_code,
        ]
    );
}
