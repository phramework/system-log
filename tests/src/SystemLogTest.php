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
        $settings = [
            'system-log' => [
                'log' => '\\Phramework\\SystemLog\\Log\\TerminalLog',
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
