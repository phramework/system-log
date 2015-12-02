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

namespace Phramework\SystemLog\APP\Log;

use \Phramework\Phramework;

/**
 * Log implementation for PHPUnit tests
 * Use setCallback to register a callback and write PHPUnit tests inside
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class PHPUnit extends \Phramework\SystemLog\Log\TerminalLog
{
    protected static $callback;
    protected static $displayOutput = false;

    /**
     * @param callable $callback
     */
    public static function setCallback($callback)
    {
        self::$callback = $callback;
    }

    /**
     * @param boolean $displayOutput
     */
    public static function setDisplayOutput($displayOutput)
    {
        self::$displayOutput = $displayOutput;
    }

    public function log($step, $data)
    {
        if (self::$callback) {
            call_user_func(
                self::$callback,
                $step,
                $data
            );
        }
        
        if (self::$displayOutput) {
            parent::log($step, $data);
        }
    }
}
