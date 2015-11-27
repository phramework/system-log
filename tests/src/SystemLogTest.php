<?php

namespace Phramework\SystemLog;

use \Phramework\Phramework;

class SystemLogTest extends \PHPUnit_Framework_TestCase
{

    private $phramework;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //force uri
        //$_SERVER['REQUEST_URI'] = '/dummy/10/';

        $settings = [
            'system-log' => [
                'log' => '\\Phramework\\SystemLog\\Log\\TerminalLog',
                'matrix' => [
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController::GET'
                        => SystemLog::LOG_REQUEST_HEADER_AGENT | SystemLog::LOG_RESPONSE_BODY | SystemLog::LOG_REQUEST_HEADERS
                ],
                'matrix-error' => [
                    '\Exception' => 0
                ],
                'database-log' => [
                    'driver' => 'postgresql',
                    'host' => '127.0.0.1',
                    'user' => 'system-log',
                    'pass' => 'pass',
                    'name' => 'username',
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
                    '/dummy/{id}',
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GETById',
                    Phramework::METHOD_ANY
                ],
            ])
        );
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
        SystemLog::register();

        $this->phramework->invoke();
    }
}
