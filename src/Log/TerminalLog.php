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

namespace Phramework\SystemLog\Log;

use \Phramework\Phramework;

/**
 * Log implementation using terminal to display log messages
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class TerminalLog implements ILog
{
    /**
     * Uses stderr to output log
     * @param string $step
     * @param object $data Log object
     */
    public function log($step, $data)
    {
        ob_start();
        $f = fopen('php://stderr', 'w');
        fputs($f, json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL);
        ob_get_contents();
        ob_end_clean();
    }

    /**
     * @param object $settings System log instance settings
     */
    public function __construct($settings)
    {
    }
}
