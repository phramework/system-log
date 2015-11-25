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

namespace Phramework\SystemLog\Log;

use \Phramework\Phramework;

/**
 * Log implementation using terminal to display log messages
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 */
class TerminalLog implements ILog
{

    public function log($step, $data)
    {
        echo PHP_EOL . $step . PHP_EOL;
        echo json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    }

    /**
     * @param array $settings Phramework settings
     * @throws \Phramework\Exceptions\ServerException
     */
    public function __construct($settings)
    {
    }
}
