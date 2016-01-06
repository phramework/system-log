<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis
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

/**
 * MY namespace
 */
namespace Phramework\SystemLog;

use \Phramework\Phramework;
use \Phramework\Extensions\StepCallback;

/**
 * SystemLog package, used to log requests and exceptions
 * Defined settings: <br/>
 * <ul>
 * <li>
 *   object system-log
 *   <ul>
 *     <li>string  log Log implentation class (full class path)</li>
 *     <li>integer body_raw_limit <i>[Optional]</i> In bytes, default is 1000000</li>
 *     <li>
 *       array   matrix <i>[Optional]</i> Set log level for each Controller::method.<br/>
 *       Constants defined in this class with prefix <strong>LOG_</strong> are used as flags to enable certain fields.
 *     </li>
 *     <li>
 *       array   matrix-exception <i>[Optional]</i> Set log level for each exception class.<br/>
 *       Constants defined in this class with prefix <strong>LOG_</strong> are used as flags to enable certain fields.
 *     </li>
 *   </ul>
 * </li>
 * </ul>
 * See [__construct](#___construct) method for an example
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 * @link https://github.com/phramework/system-log Source code
 * @link https://phramework.github.io/system-log Documentation
 */
class SystemLog
{
    /**
     * Default flag for requests
     * By default will store:
     * <ul>
     * <li>request_id</li>
     * <li>URI</li>
     * <li>method</li>
     * <li>ip_address</li>
     * <li>request_timestamp</li>
     * <li>response_timestamp</li>
     * <li>response_status_code</li>
     * <li>flags</li>
     * <li>additional_parameters</li>
     * </ul>
     */
    const LOG_STANDARD                = 0;
    /**
     * Default flag for exceptions
     * In addition to fields described in **`LOG_STANDARD`**, exceptions will store:
     * <ul>
     * <li>exception</li>
     * <li>exception_class</li>
     * <li>errors</li>
     * <li>call_trace</li>
     * </ul>
     */
    const LOG_EXCEPTION_STANDARD      = 0;
    /**
     * Will ignore this request from system log
     */
    const LOG_IGNORE                  = 1;
    /**
     * Will log user's id if request is authenticated, if not false will be written.
     * Stored as string `user_id`
     */
    const LOG_USER_ID = 2;
    /**
     * Enables log of `User-Agent` header.
     * Stored as array item in `request_headers`
     */
    const LOG_REQUEST_HEADER_AGENT    = 65536;
    /**
     * Enables log of `Referer` header.
     * Stored as array item in `request_headers`
     */
    const LOG_REQUEST_HEADER_REFERER  = 131072;
    /**
     * Enables log of `Accept` header.
     * Stored as array item in `request_headers`
     */
    const LOG_REQUEST_HEADER_ACCEPT  = 524288;
    /**
     * Enables log of `Content-Type` header.
     * Stored as array item in `request_headers`
     */
    const LOG_REQUEST_HEADER_CONTENT_TYPE  = 1048576;
    /**
     * Enables log of all request headers.
     * Stored as array item in `request_headers`
     */
    const LOG_REQUEST_HEADERS = 2097152;
    /**
     * Enables log of parsed request parameters.
     * Stored as object `request_params`
     */
    const LOG_REQUEST_PARAMS  = 4194304;
    /**
     * Enables log of raw request body if any.<br/>
     * Stored as string `request_body_raw` <br/>
     * See `body_raw_limit` setting, if length of request exceeds tis number
     * then first `body_raw_limit` characters, prefixed by `TRIMMED\n` string
     * will be used.
     * @uses https://secure.php.net/manual/en/function.filter-var.php filter_var
     * with FILTER_SANITIZE_STRING is applied to raw body
     */
    const LOG_REQUEST_BODY_RAW = 8388608;
    /**
     * Enables log of response headers.
     * Stored as array `response_headers`
     */
    const LOG_RESPONSE_HEADER = 281474976710656;
    /**
     * Enables log of response body.
     * Stored as string `response_body` <br/>
     * *NOTE* if **`LOG_RESPONSE_HEADER`** is turned off, still the `Content-Type`
     * response header will be written to response_headers.
     */
    const LOG_RESPONSE_BODY   = 562949953421312;

    /**
     * Log storage implentation
     * @var Phramework\SystemLog\Log\ILog
     */
    protected $logObject;

    /**
     * System log instance settings
     * @var object
     */
    protected $settings;

    /**
     * Create new system log instance
     * Use register method to register the instance to phramework. <br/>
     * **NOTE** that multiple system log instances can be created, this is
     * useful when different log levels for separate system reports are needed.
     * @param object $settings Settings object to initialize a system log instance
     * @example
     * ```php
     * //Your index.php file
     *
     * include __DIR__ . '/../../vendor/autoload.php';
     *
     * use \Phramework\Phramework;
     * use \Phramework\SystemLog\SystemLog;
     *
     * //Global phramework settings
     * $settings = [
     *     'system-log' => (object)[
     *         'log' => 'Phramework\\SystemLog\\Log\\TerminalLog',
     *         'body_raw_limit' => 1000,
     *         'matrix' => [
     *             'MyNamespace\\Controllers\\DummyController::GET' =>
     *                   SystemLog::LOG_REQUEST_HEADER_AGENT
     *                 | SystemLog::LOG_REQUEST_PARAMS
     *             ,
     *             'MyNamespace\\Controllers\\DummyController::POST' =>
     *                   SystemLog::LOG_REQUEST_HEADERS
     *                 | SystemLog::LOG_REQUEST_PARAMS
     *                 | SystemLog::LOG_REQUEST_BODY_RAW
     *         ]
     *         'matrix-exception' => [
     *             'Exception' =>
     *                   SystemLog::LOG_STANDARD,
     *             'Phramework\\Exceptions\\ServerException' =>
     *                   SystemLog::LOG_REQUEST_HEADER_AGENT
     *                 | SystemLog::LOG_REQUEST_PARAMS
     *         ]
     *     ]
     * ];
     *
     * //Prepare your $URIStrategy here
     *
     * //Initialize phramework
     * $phramework = new Phramework(
     *     $settings,
     *     $URIStrategy
     * );
     *
     * //Initialize a system log instance and register it
     * //before calling phramework's invoke method
     * $systemLog = new SystemLog($settings['system-log']);
     * $systemLog->register((object)[
     *     'server' => 'my server'
     * ]);
     *
     * //Invoke phramework
     * $phramework->invoke();
     * ```
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

        if (!isset($settings->body_raw_limit)) {
            $settings->body_raw_limit = 1000000;
        }
    }

    /**
     * Register system log instance to phramework
     * @param null|object $additionalParameters
     * @throws Exception
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
                $HTTPMethod /*HTTP method */,
                $headers,
                $callbackVariables,
                $invokedController,
                $invokedMethod //Class method
            ) use (
                $logObject,
                $logMatrix,
                $additionalParameters,
                $settings
            ) {
                $matrixKey = trim($invokedController, '\\') . '::' . $invokedMethod;

                $flags = (
                    isset($logMatrix[$matrixKey])
                    ? $logMatrix[$matrixKey]
                    : self::LOG_STANDARD
                );

                //If ignore flag is active, dont store anything
                if ($flags === self::LOG_IGNORE) {
                    return false;
                }

                //For common properties
                $object = SystemLog::prepareObject(
                    $flags,
                    $settings,
                    $params,
                    $HTTPMethod,
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
                $HTTPMethod,
                $headers,
                $callbackVariables,
                $errors,
                $code,
                $exception
            ) use (
                $logObject,
                $logMatrixException,
                $additionalParameters,
                $settings
            ) {
                $matrixKey = trim(get_class($exception), '\\');

                $flags = (
                    isset($logMatrixException[$matrixKey])
                    ? $logMatrixException[$matrixKey]
                    : self::LOG_STANDARD
                );

                //If ignore flag is active, dont store anything
                if ($flags === self::LOG_IGNORE) {
                    return false;
                }

                //For common properties
                $object = SystemLog::prepareObject(
                    $flags,
                    $settings,
                    $params,
                    $HTTPMethod,
                    $headers,
                    $additionalParameters
                );

                //Write specific
                $object->errors = $errors;
                $object->exception = serialize($exception);
                $object->exception_class = $matrixKey;

                $debugBacktrace = (array)(object)$exception;

                if (isset($debugBacktrace["\0Exception\0trace"])) {
                    //Get call trace from exception
                    $debugBacktrace = $debugBacktrace["\0Exception\0trace"];

                    foreach ($debugBacktrace as $k => &$v) {
                        if (isset($v['class'])) {
                            $v = $v['class'] . '::' . $v['function'];
                        } else {
                            $v = $v['function'];
                        }
                    }

                    $object->call_trace = $debugBacktrace;
                }

                return $logObject->log($step, $object);
            }
        );
    }

    /**
     * Prepare log object
     * @param  integer     $flags
     * @param  object      $settings
     * @param  object      $params
     * @param  string      $HTTPMethod
     * @param  array       $headers
     * @param  object|null $additionalParameters
     * @return object
     */
    private static function prepareObject(
        $flags,
        $settings,
        $params,
        $HTTPMethod,
        $headers,
        $additionalParameters
    ) {
        list($URI) = \Phramework\URIStrategy\URITemplate::URI();

        $object = (object)[
            'request_id' => Phramework::getRequestUUID(),
            'URI' => $URI,
            'method' => $HTTPMethod,
            'user_id' => null,
            'ip_address' => \Phramework\Models\Util::getIPAddress(),
            'request_headers' => null,
            'request_params' => null,
            'request_body_raw' => null,
            'request_timestamp' => $_SERVER['REQUEST_TIME'],
            'response_timestamp' => time(),
            'response_headers' => null,
            'response_body'    => null,
            'response_status_code' => http_response_code(),
            'exception' => null,
            'exception_class' => null,
            'errors' => null,
            'call_trace' => null,
            'flags' => $flags, //matrix flags
            'additional_parameters' => $additionalParameters
        ];

        if (($flags & self::LOG_USER_ID) !== 0) {
            $user = Phramework::getUser();
            $object->user_id = ($user ? $user->id : false);
        }

        /*
            Request flags
         */

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

        if (true || ($flags & self::LOG_REQUEST_BODY_RAW) !== 0) {


            $bodyRaw = file_get_contents('php://input'); //file_get_contents('php://input');

            if (strlen($bodyRaw) > $settings->body_raw_limit) {
                $bodyRaw = 'TRIMMED' . PHP_EOL . substr($bodyRaw, 0, $settings->body_raw_limit);
            }

            //Apply FILTER_SANITIZE_STRING
            $object->request_body_raw = \Phramework\Models\Filter::string($bodyRaw);

            //include content type headers if disabled
            if (!empty($bodyRaw)
                && ($flags & self::LOG_REQUEST_HEADERS) === 0
                && ($flags & self::LOG_REQUEST_HEADER_CONTENT_TYPE) === 0
            ) {
                $contentType = (
                    isset($headers[\Phramework\Models\Request::HEADER_CONTENT_TYPE])
                    ? $headers[\Phramework\Models\Request::HEADER_CONTENT_TYPE]
                    : null
                );

                if (empty($object->request_headers)) {
                    //make sure it's array
                    $object->request_headers = [];
                }

                $object->request_headers[
                    \Phramework\Models\Request::HEADER_CONTENT_TYPE
                ] = $contentType;
            }
        }

        $responseHeaders = new \stdClass();

        foreach (headers_list() as $header) {
            list($key, $value) = explode(': ', $header);

            $responseHeaders->{$key} = $value;
        }

        /*
            Response flags
         */

        if (($flags & self::LOG_RESPONSE_HEADER) !== 0) {
            $object->response_headers = $responseHeaders;
        }

        if (($flags & self::LOG_RESPONSE_BODY) !== 0) {
            $object->response_body = ob_get_contents();

            if (($flags & self::LOG_RESPONSE_HEADER) === 0) {
                //show content type if headers are disabled
                $object->response_headers = (object)[
                    \Phramework\Models\Request::HEADER_CONTENT_TYPE
                    => (
                        isset($responseHeaders->{\Phramework\Models\Request::HEADER_CONTENT_TYPE})
                        ? $responseHeaders->{\Phramework\Models\Request::HEADER_CONTENT_TYPE}
                        : null
                    )
                ];
            }
        }

        return $object;
    }
}
