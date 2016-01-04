<?php
/**
 * Copyright 2015 Xenofon Spafaridis.
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
use Phramework\Exceptions\ServerException;

/**
 * Log implementation using databse as store method
 * Defined settings:
 * - object system-log
 *   - object database-log
 *     - string  adapter, IAdapter's implementation class path
 *         <div class="alert alert-info">
 *         <i>Example:</i>
 *         <code>
 *         'adapter' => 'Phramework\\Database\\MySQL',
 *         </code>
 *         </div>
 *     - string host
 *     - string port
 *     - string name
 *     - string username
 *     - string password
 *     - <li>string  schema, <i>[Optional]</i>, Table's schema, default is null</li>
 *     - <li>string  table, <i>[Optional]</i>, Table's name, default is `"system-log"`</li>
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class DatabaseLog implements ILog
{
    /**
     * Table's schema, null if default is used
     * @var null|string
     */
    protected $schema = null;

    /**
     * @param string $step
     * @param array  $data
     *
     * @todo surround with try catch
     * @todo use logAdapter to store the data
     */
    public function log($step, $data)
    {
        $data->request_headers = json_encode($data->request_headers);

        $data->request_params = json_encode($data->request_params);

        $data->errors = (empty($data->errors)) ? '{}' : json_encode($data->errors);

        $data->additional_parameters =
            (empty($data->additional_parameters)) ? '{}' : json_encode($data->additional_parameters);

        $data->response_status_code = (empty($data->response_status_code)) ? '199' : $data->response_status_code;

        //return \Phramework\Database\Operations\Create::create(
        //    (array)$data,
        //    'api_log',
        //    'log_store'
        //);
        //
    }

    /**
     * Internal database adapter for logging
     * @var Phramework\Database\IAdapter
     */
    protected $logAdapter;

    /**
     * @param array $settings Phramework settings
     * @throws Exception
     */
    public function __construct($settings)
    {
        if (!isset($settings->{'database-log'})) {
            throw new ServerException(
                'Setting system-log.database-log is not set'
            );
        }

        $settingsDb = $settings->{'database-log'};

        $logAdapterNamespace = $settingsDb->adapter;

        //Initialize new adapter used to store the log queries
        $this->logAdapter = new $logAdapterNamespace(
            (array)$settingsDb
        );

        if (!($this->logAdapter instanceof \Phramework\Database\IAdapter)) {
            throw new \Exception(sprintf(
                'Class "%s" is not implementing Phramework\Database\IAdapter',
                $logAdapterNamespace
            ));
        }

        //Check if schema database setting is set
        if (isset($settingsDb->schema)) {
            $this->schema = $settingsDb->schema;
        }

        \Phramework\Database\Database::setAdapter($this->logAdapter);
    }

    public function __destruct()
    {
        try {
            $this->adapter->close();
        } catch (\Exception $e) {
        }
    }
}
