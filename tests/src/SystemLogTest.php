<?php

namespace Phramework\SystemLog;

use \Phramework\Phramework;

class SystemLogTest extends \PHPUnit_Framework_TestCase
{

    private $phramework;
    private $systemLog;
    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //force uri
        $_SERVER['REQUEST_URI'] = '/dummy/10/';
        //$_SERVER['REQUEST_URI'] = '/xdummy/10/';
        $_GET['ok'] = true;
        //$_SERVER['REQUEST_URI'] = '/dummy/';

        $settings = [
            'system-log' => [
                'log' => '\\Phramework\\SystemLog\\Log\\TerminalLog',
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
                    'driver' => 'postgresql',
                    'host' => '127.0.0.1',
                    'name' => 'system-log',
                    'password' => 'pass',
                    'username' => 'username',
                    'port' => 5432
                ]
            ]
        ];



        $this->phramework = new Phramework(
            $settings,
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

        $this->systemLog = new SystemLog($settings['system-log']);
    }

    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers Phramework\SystemLog\SystemLog::register
     */
    public function testRegister()
    {
        $this->systemLog->register([
            'API' => 'phpunit'
        ]);

        $this->phramework->invoke();
    }
}
