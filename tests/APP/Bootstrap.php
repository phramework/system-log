<?php
/**
 * Copyright 2015 Xenofon Spafaridis.
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

use Phramework\Phramework;
use Phramework\SystemLog\SystemLog;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class Bootstrap
{
    public static function getSettings()
    {
        $settings = [
            'debug' => true,
            'system-log' => (object)[
                'log' => 'Phramework\\SystemLog\\APP\\Log\\PHPUnit',
                'body_raw_limit' => 1000,
                'matrix' => [
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController::GET' =>
                              SystemLog::LOG_REQUEST_HEADER_AGENT
                            | SystemLog::LOG_REQUEST_PARAMS
                            | SystemLog::LOG_RESPONSE_BODY
                            | SystemLog::LOG_REQUEST_HEADERS,
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController::GETById' =>
                              SystemLog::LOG_REQUEST_HEADER_AGENT
                            | SystemLog::LOG_REQUEST_HEADER_REFERER
                        //    | SystemLog::LOG_REQUEST_HEADERS
                            | SystemLog::LOG_REQUEST_PARAMS
                            | SystemLog::LOG_RESPONSE_BODY
                            //| SystemLog::LOG_RESPONSE_HEADER
                            ,
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController::POST' =>
                              SystemLog::LOG_REQUEST_BODY_RAW
                            ,
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController::PUT' =>
                              SystemLog::LOG_IGNORE
                ],
                'matrix-exception' => [
                    'Exception' =>
                              SystemLog::LOG_STANDARD,
                    'Phramework\\Exceptions\\ServerException' =>
                              SystemLog::LOG_REQUEST_HEADER_AGENT
                            | SystemLog::LOG_REQUEST_PARAMS
                            | SystemLog::LOG_RESPONSE_BODY
                            | SystemLog::LOG_REQUEST_HEADERS,
                    'Phramework\\Exceptions\\MissingParametersException' =>
                              SystemLog::LOG_REQUEST_HEADER_AGENT
                            | SystemLog::LOG_REQUEST_PARAMS
                            | SystemLog::LOG_REQUEST_BODY_RAW
                            | SystemLog::LOG_RESPONSE_BODY
                            | SystemLog::LOG_REQUEST_HEADERS,
                    'Phramework\\Exceptions\\NotFoundException' =>
                              SystemLog::LOG_USER_ID
                            | SystemLog::LOG_REQUEST_BODY_RAW
                            | SystemLog::LOG_RESPONSE_BODY
                ],
                'database-log' => (object)[
                    'adapter' => 'Phramework\\Database\\PostgreSQL',
                    'host' => '127.0.0.1',
                    'name' => 'db_name',
                    'password' => 'db_pass',
                    'username' => 'db_user',
                    'port' => 5432,
                    //'schema' =>
                ]
            ],
        ];

        if (file_exists(__DIR__.'/localsettings.php')) {
            include __DIR__.'/localsettings.php';
        }

        return $settings;
    }

    /**
     * Prepare a phramework instance.
     *
     * @uses Bootstrap::getSettings() to fetch the settings
     *
     * @return Phramework
     */
    public static function prepare($webserver = false)
    {
        if (!$webserver) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['REQUEST_TIME'] = time() -1;
        }

        $phramework = new Phramework(
            self::getSettings(),
            new \Phramework\URIStrategy\URITemplate([
                [
                    '/',
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GET',
                    Phramework::METHOD_GET,
                ],
                [
                    '/dummy/',
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GET',
                    Phramework::METHOD_GET,
                ],
                [
                    '/dummy/',
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'POST',
                    Phramework::METHOD_POST,
                ],
                [
                    '/dummy/{id}',
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'PUT',
                    Phramework::METHOD_PUT,
                ],
                [
                    '/dummy/{id}',
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GETById',
                    Phramework::METHOD_GET,
                ],
            ])
        );

        return $phramework;
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @link https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
     */
    public static function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
