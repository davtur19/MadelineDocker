<?php

use danog\MadelineProto\API;
use danog\MadelineProto\Logger;

require_once '/app/lib/madeline/vendor/autoload.php';

$settings = [
        'logger' => [
                'logger_level' => Logger::ULTRA_VERBOSE,
        ],
        'serialization' => [
                'serialization_interval' => 60 * 2,
        ],
];

$MadelineProto = new \danog\MadelineProto\API('session.madeline', $settings);
$MadelineProto->start();
$MadelineProto->setNoop();
$MadelineProto->loop();
