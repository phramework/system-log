<?php

use \Phramework\SystemLog\SystemLog;

include __DIR__ . '/../../vendor/autoload.php';

$settings = \Phramework\SystemLog\APP\Bootstrap::getSettings();

$phramework = \Phramework\SystemLog\APP\Bootstrap::prepare(true);

//$settings['system-log']->log = 'Phramework\\SystemLog\\Log\\TerminalLog';
//$settings['system-log']->log = 'Phramework\\SystemLog\\Log\\DatabaseLog';

$systemLog = new SystemLog($settings['system-log']);

$systemLog->register((object)[
    'runtime' => 'php server'
]);

$phramework->invoke();
