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
 * - array system-log
 *   - string log Log implentation class (full class path)
 *   - array  matrix  *[Optional]*
 *   - array  matrix-exception *[Optional]*
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class SystemLog
{

    const LOG_STANDARD                = 0;
    const LOG_EXCEPTION_STANDARD      = 0;

    /**
     * Will not store this request to log
     */
    const LOG_IGNORE                  = 1;
    const LOG_USER_ID = 2;

    /**
     * User-Agent
     */
    const LOG_REQUEST_HEADER_AGENT    = 65536;
    /**
     * Referer
     */
    const LOG_REQUEST_HEADER_REFERER  = 131072;
    /**
     * Accept
     */
    const LOG_REQUEST_HEADER_ACCEPT  = 524288;
    /**
     * Accept
     */
    const LOG_REQUEST_HEADER_CONTENT_TYPE  = 1048576;

    const LOG_REQUEST_HEADERS = 2097152;
    const LOG_REQUEST_PARAMS  = 4194304;
    const LOG_REQUEST_BODY_RAW = 8388608;

    const LOG_RESPONSE_HEADER = 281474976710656;
    const LOG_RESPONSE_BODY   = 562949953421312;

    /**
     * @var Phramework\SystemLog\Log\ILog
     */
    protected $logObject;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Create new system log
     * @param object $settings
     */
    public function __construct($settings)
    {
        if (is_array($settings)) {
            $settings = (object)($setting);
        }

        $this->settings = $settings;

        //Check if system-log setting array is set
        if (!isset($settings->log)) {
            throw new \Phramework\Exceptions\ServerException(
                'system-log.log setting is not set!'
            );
        }

        $logNamespace = $settings->log;

        //Create new log implemtation object
        $this->logObject = new $logNamespace(
            $settings
        );

        if (!($this->logObject instanceof \Phramework\SystemLog\Log\ILog)) {
            throw new \Exception(
                'Class is not implementing Phramework\SystemLog\Log\ILog'
            );
        }
    }

    private static function prepareObject(
        $flags,
        $params,
        $method,
        $headers,
        $additionalParameters
    ) {
        list($URI) = \Phramework\URIStrategy\URITemplate::URI();

        $object = (object)[
            'request_id' => Phramework::getRequestUUID(),
            'URI' => $URI,
            'method' => $method,
            'user_id' => null,
            'errors' => null,
            'request_headers' => null,
            'request_params' => null,
            'request_body_raw' => null,
            'request_timestamp' => $_SERVER['REQUEST_TIME'],
            'response_headers' => null,
            'response_body'    => null,
            'response_timestamp' => time(),
            'response_status_code' => http_response_code(),
            'exception' => null,
            'exception_class' => null,
            'call_trace' => null,
            'flags' => $flags,
            'additional_parameters' => $additionalParameters
        ];

        if (($flags & self::LOG_USER_ID) !== 0) {
            $user = Phramework::getUser();
            $object->user_id = ($user ? $user->id : false);
        }

        if (($flags & self::LOG_REQUEST_HEADERS) !== 0) {
            //Asterisk authorization header value except schema
            if (isset($headers['Authorization'])) {
                list($authorizationSchema) = sscanf($headers['Authorization'], '%s %s');

                $headers['Authorization'] = $authorizationSchema . ' ***';
            }

            $object->request_headers = $headers;
        } else {
            $request_headers = [];

            if (($flags & self::LOG_REQUEST_HEADER_CONTENT_TYPE) !== 0) {
                //Write content type
                $request_headers[\Phramework\Models\Request::HEADER_CONTENT_TYPE] = (
                    isset($headers[\Phramework\Models\Request::HEADER_CONTENT_TYPE])
                    ? $headers[\Phramework\Models\Request::HEADER_CONTENT_TYPE]
                    : null
                );
            }

            if (($flags & self::LOG_REQUEST_HEADER_AGENT) !== 0) {
                $request_headers['User-Agent'] = (
                    isset($headers['User-Agent'])
                    ? $headers['User-Agent']
                    : null
                );
            }

            if (($flags & self::LOG_REQUEST_HEADER_REFERER) !== 0) {
                $request_headers['Referer'] = (
                    isset($headers['Referer'])
                    ? $headers['Referer']
                    : null
                );
            }

            if (($flags & self::LOG_REQUEST_HEADER_ACCEPT) !== 0) {
                $request_headers['Accept'] = (
                    isset($headers['Accept'])
                    ? $headers['Accept']
                    : null
                );
            }

            if (!empty($request_headers)) {
                $object->request_headers = $request_headers;
            }
        }

        if (($flags & self::LOG_REQUEST_PARAMS) !== 0) {
            $object->request_params = $params;
        }

        if (($flags & self::LOG_REQUEST_BODY_RAW) !== 0) {
            
            $object->request_body_raw = file_get_contents('php://input');
        }

        if (($flags & self::LOG_RESPONSE_HEADER) !== 0) {
            $object->response_headers = headers_list();
        }

        if (($flags & self::LOG_RESPONSE_BODY) !== 0) {
            $object->response_body = ob_get_contents();

            if (($flags & self::LOG_RESPONSE_HEADER) === 0) {
                //show content type if headers are disabled
                $headersList = headers_list();
                $object->response_headers = array_values(array_filter(
                    $headersList,
                    function ($h) {
                        return \Phramework\Models\Util::beginsWith(
                            $h,
                            \Phramework\Models\Request::HEADER_CONTENT_TYPE
                        );
                    }
                ));
            }
        }

        return $object;
    }

    /**
     * Register callbacks
     * @param null|object $additionalParameters
     */
    public function register($additionalParameters = null)
    {
        if ($additionalParameters && !is_object($additionalParameters)) {
            throw new \Exception('additionalParameters must be an object');
        }

        $settings = $this->settings;

        $logMatrix          = (array)$settings->matrix;
        $logMatrixException = (array)$settings->{'matrix-exception'};

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

                //For common properties
                $object = SystemLog::prepareObject(
                    $flags,
                    $params,
                    $method,
                    $headers,
                    $additionalParameters
                );

                //Write specific

                return $logObject->log($step, $object);
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

                //For common properties
                $object = SystemLog::prepareObject(
                    $flags,
                    $params,
                    $method,
                    $headers,
                    $additionalParameters
                );

                //Write specific
                $object->errors = $errors;
                $object->exception = serialize($exception);
                $object->exception_class = $matrixKey;

                $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

                //Remove current log function call
                //Remove QueryLogAdapter execute* function call
                //array_splice($debugBacktrace, 0, 4);

                foreach ($debugBacktrace as $k => &$v) {
                    if (isset($v['class'])) {

                        $v = $v['class'] . '::' . $v['function'];
                    } else {
                        $v = $v['function'];
                    }
                }

                $object->call_trace = $debugBacktrace;

                return $logObject->log($step, $object);
            }
        );
    }
}
