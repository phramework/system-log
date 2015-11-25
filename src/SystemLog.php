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
 * JWT authentication implementation for phramework
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 */
class SystemLog
{
    /**
     * @var \Phramework\SystemLog\Log\ILog
     */
    protected static $log;

    /**
     * Register callbacks
     */
    public static function register()
    {

        $logNamespace = Phramework::getSetting('system-log', 'log');

        if (!$logNamespace) {
            throw new \Phramework\Exceptions\ServerException(
                'system-log log setting is not set!'
            );
        }

        self::$log = $log = new $logNamespace(
            Phramework::getSetting('system-log')
        );

        if (!($log instanceof \Phramework\SystemLog\Log\ILog)) {
            throw new \Exception(
                'Class is not implementing \Phramework\SystemLog\Log\ILog'
            );
        }

        //Register step callbacks

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
            ) use ($log) {
                $object = [
                    'controller' => $invokedController,
                    'method' => $invokedMethod
                ];

                //echo json_encode($object, JSON_PRETTY_PRINT) . PHP_EOL;

                $log->log($step, $object);
            }
        );

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
            ) use ($log) {
                $object = [
                    'code' => $code,
                    'errors' => $errors
                ];

                //echo json_encode($object, JSON_PRETTY_PRINT) . PHP_EOL;

                $log->log($step, $object);
            }
        );
    }
}
