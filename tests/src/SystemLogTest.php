<?php

namespace Phramework\SystemLog;

use \Phramework\Phramework;

class SystemLogTest extends \PHPUnit_Framework_TestCase
{

    private $phramework;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * @todo update base
     */
    protected function setUp()
    {
        $settings = [];

        $this->phramework = new Phramework(
            $settings,
            new \Phramework\URIStrategy\URITemplate([])
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
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
