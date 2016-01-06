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
 * Log implementation using databse as storage
 * Defined settings:<br/>
 * <ul>
 * <li>
 *   object system-log
 *   <ul>
 *   <li>
 *     object database-log
 *     <ul>
 *       <li>string  adapter, IAdapter's implementation class (full class path)</li>
 *       <li>string host</li>
 *       <li>string port</li>
 *       <li>string name</li>
 *       <li>string username</li>
 *       <li>string password</li>
 *       <li>string  schema, <i>[Optional]</i>, Table's schema, default is null</li>
 *       <li>string  table, <i>[Optional]</i>, Table's name, default is "system-log"</li>
 *     </ul>
 *   </li>
 *   </ul>
 * </li>
 * </ul>
 * To use DatabaseLog:
 * <br/>- Set system-log's log to `'Phramework\\SystemLog\\APP\\Log\\DatabaseLog'`
 * <br/>- Add database-log objecto to your settings
 * <br/>See this class's [__construct](#___construct) method for an example.
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @since 0.0.0
 */
class DatabaseLog implements ILog
{
    /**
     * Table's schema, null if default is used
     * @var null|string
     */
    protected $schema = null;

    /**
     * Table's
     * @var string
     */
    protected $table = 'system_log';

    /**
     * Internal database adapter used to log object to database
     * @var Phramework\Database\IAdapter
     */
    protected $logAdapter;

    /**
     * @param object $settings System log instance settings
     * @throws Exception
     * @throws ServerException
     * @example
     * ```php
     * // To use DatabaseLog
     * // Set system-log's setting log to 'Phramework\\SystemLog\\APP\\Log\\DatabaseLog'
     * // Add database-log object to your system-log settings
     * $settings = [
     *     'system-log' => (object)[
     *         'log' => 'Phramework\\SystemLog\\APP\\Log\\DatabaseLog',
     *         'database-log' => (object)[
     *             'adapter'  => 'Phramework\\Database\\PostgreSQL',
     *             'host'     => '127.0.0.1',
     *             'name'     => 'db_name',
     *             'password' => 'db_pass',
     *             'username' => 'db_user',
     *             'port'     => 5432,
     *             'schema'   => 'log'
     *         ]
     *     ]
     * ];
     * ```
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
            throw new ServerException(sprintf(
                'Class "%s" is not implementing Phramework\Database\IAdapter',
                $logAdapterNamespace
            ));
        }

        //Check if schema database setting is set
        if (isset($settingsDb->schema)) {
            $this->schema = $settingsDb->schema;
        }

        //Check if table database setting is set
        if (isset($settingsDb->table)) {
            $this->table = $settingsDb->table;
        }

        \Phramework\Database\Database::setAdapter($this->logAdapter);
    }

    /**
     * @param string $step
     * @param object $data Log object
     *
     * @todo surround with try catch
     * @return string Returns the id of inserted record
     */
    public function log($step, $data)
    {
        //Convert data, so they can be inserted in database

        $data->request_headers = (
            empty($data->request_headers)
            ? null
            : json_encode($data->request_headers)
        );

        $data->request_params = (
            empty($data->request_params)
            ? null
            : json_encode($data->request_params)
        );

        $data->errors = (
            empty($data->errors)
            ? null
            : json_encode($data->errors)
        );

        $data->additional_parameters = (
            empty($data->additional_parameters)
            ? null
            : json_encode($data->additional_parameters)
        );

        $data->call_trace =(
            empty($data->call_trace)
            ? null
            : json_encode($data->call_trace)
        );

        $data->response_status_code =  (
            empty($data->response_status_code)
            ? null
            : $data->response_status_code
        );

        //Insert log object to database

        $attributes = (
            is_object($data)
            ? (array)$data
            : $data
        );

        $schema = $this->schema;
        $table  = $this->table;

        //prepare query
        $queryKeys   = implode('" , "', array_keys($attributes));
        $queryParameterString = trim(str_repeat('?,', count($attributes)), ',');
        $queryValues = array_values($attributes);

        $query = 'INSERT INTO ';

        if ($schema !== null) {
            $query .= sprintf('"%s"."%s"', $schema, $table);
        } else {
            $query .= sprintf('"%s"', $table);
        }

        $query .= sprintf(
            ' ("%s") VALUES (%s)',
            $queryKeys,
            $queryParameterString
        );

        if ($this->logAdapter->getAdapterName() == 'postgresql') {
            $query .= ' RETURNING id';
            $id = $this->logAdapter->executeAndFetch($query, $queryValues);
            return $id['id'];
        }

        return $this->logAdapter->executeLastInsertId($query, $queryValues);
    }

    public function __destruct()
    {
        try {
            if ($this->logAdapter) {
                $this->logAdapter->close();
            }
        } catch (\Exception $e) {
        }
    }
}
