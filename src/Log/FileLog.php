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
use \Phramework\Exceptions\ServerException;

/**
 * Log implementation using file as storage method
 * Defined settings: <br/>
 * <ul>
 * <li>
 *   object system-log[]
 *   <ul>
 *     <li>
 *       object file-log[]
 *       <ul>
 *         <li>string path File path to store log</li>
 *       </ul>
 *     </li>
 *   </ul>
 * </li>
 * </ul>
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class FileLog implements ILog
{
    /**
     * Log file's path
     * @var string
     */
    protected $path;

    /**
     * @param object $settings System log instance settings
     * @throws ServerException
     */
    public function __construct($settings)
    {
        if (!isset($settings->{'file-log'})) {
            throw new ServerException(
                'Setting system-log.file-log is not set'
            );
        }

        if (!isset($settings->{'file-log'}->path)) {
            throw new ServerException(
                'Setting system-log.file-log.path is not set'
            );
        }

        $this->path = $settings->{'file-log'}->path;

        if (!is_writable($this->path)) {
            throw new ServerException(sprintf(
                'File "%s" is not writeble',
                $this->path
            ));
        }
    }

    /**
     * @param string $step
     * @param object $data Log object
     */
    public function log($step, $data)
    {
        file_put_contents($this->path, json_encode($data) . PHP_EOL, FILE_APPEND);
    }
}
