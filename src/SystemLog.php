<?php
/**
 * Copyright 2015 Spafaridis Xenofon
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
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 */
class SystemLog
{
    /**
     * @var \Phramework\SystemLog\Log\ILog
     */
    protected static $logObject;

    /**
     * Register callbacks
     */
    public static function register()
    {
        //Get settings
        $logNamespace = Phramework::getSetting('system-log', 'log');

        //Check if system-log setting array is set
        if (!$logNamespace) {
            throw new \Phramework\Exceptions\ServerException(
                'system-log log setting is not set!'
            );
        }

        //Create new log implemtation object
        self::$logObject = $logObject = new $logNamespace(
            Phramework::getSetting('system-log')
        );

        if (!($logObject instanceof \Phramework\SystemLog\Log\ILog)) {
            throw new \Exception(
                'Class is not implementing \Phramework\SystemLog\Log\ILog'
            );
        }

        /*
         * Register step callbacks
         */

        //Register after call URIStrategy (after controller/method is invoked) callback
        Phramework::$stepCallback->add(
            StepCallback::STEP_AFTER_CALL_URISTRATEGY,
            function (
                $step,
                $params,
                $method,
                $headers,
                $callbackVariables,
                $invokedController,
                $invokedMethod
            ) use ($logObject) {
                $object = [
                    'controller' => $invokedController,
                    'method' => $invokedMethod
                ];

                //echo json_encode($object, JSON_PRETTY_PRINT) . PHP_EOL;

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
            ) use ($logObject) {
                $object = [
                    'code' => $code,
                    'errors' => $errors
                ];

                //list($URL) = self::URI();

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
        $REDIRECT_QUERY_STRING =
            isset($_SERVER['QUERY_STRING'])
            ? $_SERVER['QUERY_STRING']
            : '';

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
