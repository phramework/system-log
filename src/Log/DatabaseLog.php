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
 * Log implementation using databse as store method
 * Defined settings:
 * - system-log[]
 *   - database-log[]
 *     - driver
 *     - host
 *     - user
 *     - pass
 *     - name
 *     - port
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 */
class DatabaseLog implements ILog
{
    public function log($step, $data)
    {

    }

    /**
     * @param array $settings Phramework settings
     * @throws \Phramework\Exceptions\ServerException
     */
    public function __construct($settings)
    {
        if (!isset($settings['database-log'])) {
            throw new \Phramework\Exceptions\ServerException(
                'Setting system-log.database-log is not set'
            );
        }
    }
}
