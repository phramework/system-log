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

namespace Phramework\SystemLog\APP;

use \Phramework\Phramework;
use \Phramework\SystemLog\SystemLog;

/**
 * Log implementation for PHPUnit tests
 * Use setCallback to register a callback and write PHPUnit tests inside
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class Bootstrap
{
    public static function getSettings()
    {
        return [
            'system-log' => [
                'log' => '\\Phramework\\SystemLog\\APP\\Log\\PHPUnit',
                'matrix' => [
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController::GET'
                        =>    SystemLog::LOG_REQUEST_HEADER_AGENT
                            | SystemLog::LOG_REQUEST_PARAMS
                            | SystemLog::LOG_RESPONSE_BODY
                            | SystemLog::LOG_REQUEST_HEADERS,
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController::GETById'
                        =>    SystemLog::LOG_REQUEST_PARAMS
                            | SystemLog::LOG_RESPONSE_BODY
                ],
                'matrix-exception' => [
                    'Exception'
                        =>    SystemLog::LOG_STANDARD,
                    'Phramework\\Exceptions\\ServerException'
                        =>    SystemLog::LOG_REQUEST_HEADER_AGENT
                            | SystemLog::LOG_REQUEST_PARAMS
                            | SystemLog::LOG_RESPONSE_BODY
                            | SystemLog::LOG_REQUEST_HEADERS,
                    'Phramework\\Exceptions\\NotFoundException'
                        =>    SystemLog::LOG_STANDARD
                            | SystemLog::LOG_USER_ID,
                ],
                'database-log' => [
                    'adapter' => 'postgresql',
                    'host' => '127.0.0.1',
                    'name' => 'system-log',
                    'password' => 'pass',
                    'username' => 'username',
                    'port' => 5432
                ]
            ]
        ];
    }

    /**
     * Prepare a phramework instance
     * @uses Bootstrap::getSettings() to fetch the settings
     * @return Phramework
     */
    public static function prepare()
    {
        $phramework = new Phramework(
            self::getSettings(),
            new \Phramework\URIStrategy\URITemplate([
                [
                    '/',
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GET',
                    Phramework::METHOD_ANY
                ],
                [
                    '/dummy/',
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GET',
                    Phramework::METHOD_ANY
                ],
                [
                    '/dummy/{id}',
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GETById',
                    Phramework::METHOD_ANY
                ],
            ])
        );

        return $phramework;
    }
}
