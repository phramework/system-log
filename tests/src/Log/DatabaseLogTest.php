<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis.
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
namespace Phramework\SystemLog\Log;

use Phramework\Phramework;
use Phramework\SystemLog\SystemLog;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class DatabaseLogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SystemLog
     */
    protected $systemLog;
    protected $phramework;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $settings = \Phramework\SystemLog\APP\Bootstrap::getSettings();

        $this->phramework = \Phramework\SystemLog\APP\Bootstrap::prepare();

        $settings['system-log']->log = 'Phramework\\SystemLog\\Log\\DatabaseLog';

        $this->systemLog = new SystemLog($settings['system-log']);
    }

    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers \Phramework\SystemLog\Log\DatabaseLog::log
     */
    public function testLog()
    {
        //Force URI route
        $_SERVER['REQUEST_URI'] = '/dummy/1';
        $_SERVER['REQUEST_METHOD'] = Phramework::METHOD_GET;

        $this->systemLog->register();
        
        $this->phramework->invoke();
    }
}
