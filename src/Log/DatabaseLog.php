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
 * - system-log[]
 *   - database-log[]
 *     - adapter
 *     - host
 *     - port
 *     - name
 *     - username
 *     - password.
 *
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class DatabaseLog implements ILog
{
    /**
     * @param string $step
     * @param array  $data
     *
     * @todo surround with try catch
     */
    public function log($step, $data)
    {
        $data->request_headers = json_encode($data->request_headers);
        $data->request_params = json_encode($data->request_params);
        $data->errors = (empty($data->errors)) ? '{}' : json_encode($data->errors);
        $data->additional_parameters =
            (empty($data->additional_parameters)) ? '{}' : json_encode($data->additional_parameters);
        $data->response_status_code = (empty($data->response_status_code)) ? '199' : $data->response_status_code;

        return \Phramework\Database\Operations\Create::create(
          (array) $data,
          'api_log',
          'log_store'
        );
    }

    protected $adapter;

    /**
     * @param array $settings Phramework settings
     *
     * @throws \Phramework\Exceptions\ServerException
     */
    public function __construct($settings)
    {
        if (!isset($settings['database-log'])) {
            throw new ServerException(
                'Setting system-log.database-log is not set'
            );
        }

        $settingsDb = $settings['database-log'];

        $adapterName = $settingsDb['adapter'];

        switch ($adapterName) {
            case 'postgresql':
                $this->adapter = new \Phramework\Database\PostgreSQL(
                    $settingsDb
                );
                break;
            case 'mysql':
            case 'mariadb':
                $this->adapter = new \Phramework\Database\MySQL(
                    $settingsDb
                );
                break;
            default:
                throw new ServerException(sprintf(
                    'Adapter "%s" is not supported!',
                    $adapterName
                ));
                break;
        }

        \Phramework\Database\Database::setAdapter($this->adapter);
    }

    public function __destruct()
    {
        try {
            $this->adapter->close();
        } catch (\Exception $e) {
        }
    }
}
