<?php
/**
 * Copyright 2015 - 2016 Xenofon Spafaridis
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

namespace Phramework\SystemLog\APP\Controllers;

use \Phramework\Phramework;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class DummyController
{
    public static function GET($params, $method, $headers)
    {
        \Phramework\Phramework::view(
            [
                'data' => [
                    [
                        'type' => 'dummy',
                        'id' => 3
                    ],
                    [
                        'type' => 'dummy',
                        'id' => 4
                    ]
                ]
            ]
        );
    }

    public static function GETById($params, $method, $headers, $id)
    {
        \Phramework\Phramework::view([
            'data' => [
                'type' => 'dummy',
                'id' => $id
            ]
        ]);
    }

    public static function POST($params, $method, $headers)
    {
        throw new \Phramework\Exceptions\MissingParametersException(['ok']);
    }

    public static function PUT($params, $method, $headers, $id)
    {
        \Phramework\Models\Response::noContent();
    }
}
