<?php
/**
 * Copyright 2015 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Phramework\SystemLog;

use \Phramework\Phramework;
use \Phramework\Extensions\StepCallback;

/**
 * SystemLog package, used to log requests and exceptions
 * Defined settings:
 * - system-log[]
 *   - log Log implentation class (full class path)
 *   - matrix[]
 *   - matrix-exception[]
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class SystemLog
{

    const LOG_STANDARD                = 0;
    /**
     * Will not store this request to log
     */
    const LOG_IGNORE                  = 1;
    const LOG_USER_ID = 2;
    const LOG_REQUEST_HEADER_AGENT    = 4;
    const LOG_REQUEST_HEADER_REFERER  = 8;



    const LOG_REQUEST_HEADERS = 64;
    const LOG_REQUEST_PARAMS  = 128;

    const LOG_RESPONSE_HEADER = 1024;
    const LOG_RESPONSE_BODY   = 2048;

    /**
     * @var \Phramework\SystemLog\Log\ILog
     */
    protected $logObject;
    /**
     * @var array
     */
    protected $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;


        //Check if system-log setting array is set
        if (!isset($settings['log'])) {
            throw new \Phramework\Exceptions\ServerException(
                'system-log.log setting is not set!'
            );
        }

        $logNamespace = $settings['log'];

        //Create new log implemtation object
        $this->logObject = new $logNamespace(
            $settings
        );

        if (!($this->logObject instanceof \Phramework\SystemLog\Log\ILog)) {
            throw new \Exception(
                'Class is not implementing \Phramework\SystemLog\Log\ILog'
            );
        }
    }

    /**
     * Register callbacks
     */
    public function register($additionalParameters = [])
    {
        $settings = $this->settings;
        
        $logMatrix          = $settings['matrix'];
        $logMatrixException = $settings['matrix-exception'];

        $logObject = $this->logObject;

        /*
         * Register step callbacks
         */

        //Register after call URIStrategy (after controller/method is invoked) callback
        Phramework::$stepCallback->add(
            StepCallback::STEP_AFTER_CALL_URISTRATEGY,
            function (
                $step,
                $params,
                $method /*HTTP method */,
                $headers,
                $callbackVariables,
                $invokedController,
                $invokedMethod //Class method
            ) use (
                $logObject,
                $logMatrix,
                $additionalParameters
            ) {
                list($URI) = self::URI();

                $matrixKey = trim($invokedController, '\\') . '::' . $invokedMethod;

                $flags = (
                    isset($logMatrix[$matrixKey])
                    ? $logMatrix[$matrixKey]
                    : self::LOG_STANDARD
                );

                //If ignore flag is active, dont store anything
                if (($flags & self::LOG_IGNORE) !== 0) {
                    return;
                }

                $object = [
                    'URI' => $URI,
                    'method' => $method,
                    'user_id' => null,
                    'request_headers' => null,
                    'request_params' => null,
                    'request_timestamp' => $_SERVER['REQUEST_TIME'],
                    'response_headers' => null,
                    'response_body'    => null,
                    'response_timestamp' => time(),
                    'response_status_code' => http_response_code(),
                    'flags' => $flags, /*Used log flags*/
                    'additional_parameters' => $additionalParameters,
                    'errors' => null, /*Used in errors*/
                    'exception' => null /*Used in errors*/
                ];

                if (($flags & self::LOG_USER_ID) !== 0) {
                    $user = \Phramework\Phramework::getUser();
                    $object['user_id'] = ($user ? $user->id : false);
                }

                if (($flags & self::LOG_REQUEST_HEADERS) !== 0) {
                    $object['request_headers'] = $headers;
                }

                if (($flags & self::LOG_REQUEST_PARAMS) !== 0) {
                    $object['request_params'] = $params;
                }

                if (($flags & self::LOG_RESPONSE_HEADER) !== 0) {
                    //$object['response_headers'] = headers_list();
                }

                if (($flags & self::LOG_RESPONSE_BODY) !== 0) {
                    $object['response_body'] = ob_get_contents();

                    if (($flags & self::LOG_RESPONSE_HEADER) === 0) {

                        //$headers_list = headers_list();
                        //$object['response_headers'] = $headers_list;
                    }
                }

                $logObject->log($step, $object);
            }
        );

        //Register on error callback
        Phramework::$stepCallback->add(
            StepCallback::STEP_ERROR,
            function (
                $step,
                $params,
                $method,
                $headers,
                $callbackVariables,
                $errors,
                $code,
                $exception
            ) use (
                $logObject,
                $logMatrixException,
                $additionalParameters
            ) {
                $matrixKey = get_class($exception);

                $flags = (
                    isset($logMatrixException[$matrixKey])
                    ? $logMatrixException[$matrixKey]
                    : self::LOG_STANDARD
                );

                //If ignore flag is active, dont store anything
                if (($flags & self::LOG_IGNORE) !== 0) {
                    return;
                }

                list($URI) = self::URI();

                $object = [
                    'URI' => $URI,
                    'method' => $method,
                    'user_id' => null,
                    'errors' => $errors,
                    'request_headers' => null,
                    'request_params' => null,
                    'request_timestamp' => $_SERVER['REQUEST_TIME'],
                    'response_headers' => null,
                    'response_body'    => null,
                    'response_timestamp' => time(),
                    'response_status_code' => http_response_code(),
                    'exception' => $matrixKey,
                    'flags' => $flags,
                    'additional_parameters' => $additionalParameters
                ];

                if (($flags & self::LOG_USER_ID) !== 0) {
                    $user = \Phramework\Phramework::getUser();
                    $object['user_id'] = ($user ? $user->id : false);
                }

                $logObject->log($step, $object);
            }
        );
    }

    /**
     * Helper method
     * Get current URI and GET parameters from the requested URI
     * @return string[2] Returns an array with current URI and GET parameters
     */
    public static function URI()
    {
        $REDIRECT_QUERY_STRING = (
            isset($_SERVER['QUERY_STRING'])
            ? $_SERVER['QUERY_STRING']
            : ''
        );

        $REDIRECT_URL = '';

        if (isset($_SERVER['REQUEST_URI'])) {
            $url_parts = parse_url($_SERVER['REQUEST_URI']);
            $REDIRECT_URL = $url_parts['path'];
        }

        $URI = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        $URI = '/' . trim(str_replace($URI, '', $REDIRECT_URL), '/');
        $URI = urldecode($URI) . '/';

        $URI = trim($URI, '/');

        $parameters = [];

        //Extract parametrs from QUERY string
        parse_str($REDIRECT_QUERY_STRING, $parameters);

        return [$URI, $parameters];
    }
}
