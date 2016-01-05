# system-log
system-log environment for phramework

## Usage
(**NOTE** this will work when the repo will become public!)

```bash
composer require phramework/system-log
```

Edit your application's `index.php`, create a new SystemLog object and call it's `register` method before phramework is invoked.

For example:

```php
<?php
use \Phramework\Phramework;
use \Phramework\SystemLog;

$settings = [
    'system-log' => [
        'log' => '\\Phramework\\SystemLog\\Log\\TerminalLog',
        'matrix' => [
            'Me\\APP\\Controllers\\DummyController::GET'
                =>    SystemLog::LOG_REQUEST_HEADER_AGENT
                    | SystemLog::LOG_REQUEST_PARAMS
        ],
        'matrix-exception' => [
            'Exception'
                =>    SystemLog::LOG_STANDARD,
            'Phramework\\Exceptions\\ServerException'
                =>    SystemLog::LOG_REQUEST_HEADER_AGENT
                    | SystemLog::LOG_REQUEST_PARAMS
                    | SystemLog::LOG_RESPONSE_BODY
                    | SystemLog::LOG_REQUEST_HEADERS
        ]
    ]
];

$phramework = new Phramework(
    $settings,
    new \Phramework\URIStrategy\URITemplate([])
);

$systemLog = new SystemLog($settings['system-log']);
$systemLog->register();

$phramework->invoke();
```

## Development
### Install

```bash
composer update
```

### Test and lint code

```bash
composer lint
composer test
```

# License
Copyright 2015 - 2016 Xenofon Spafaridis

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

```
http://www.apache.org/licenses/LICENSE-2.0
```

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
