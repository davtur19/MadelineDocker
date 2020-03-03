<?php

use danog\MadelineProto\API;
use danog\MadelineProto\EventHandler;
use danog\MadelineProto\Exception;
use danog\MadelineProto\Logger;
use danog\MadelineProto\RPCErrorException;

require_once '/app/lib/madeline/vendor/autoload.php';

class MyEventHandler extends EventHandler
{
    const SESSION_NAME = 'session';
    // user id
    private const ADMIN = [1234];
    // replace them with yours https://core.telegram.org/api/obtaining_api_id
    const API_ID = 123;
    const API_HASH = 'abcdefg12345';

    public function getReportPeers()
    {
        return self::ADMIN;
    }

    public function onUpdateNewChannelMessage(array $update): \Generator
    {
        return $this->onUpdateNewMessage($update);
    }

    public function onUpdateNewMessage(array $update): \Generator
    {
        /*if ($update['message']['_'] === 'messageEmpty' || $update['message']['out'] ?? false) {
            return;
        }*/

        if ((!isset($update['message']['from_id'])) or (!in_array($update['message']['from_id'], self::ADMIN))) {
            return;
        }

        if (isset($update['message']['message']) and $update['message']['message'] === '*help') {
            $this->messages->sendMessage([
                    'peer' => $update,
                    'message' => 'Comandi: *help *ping *info *dump *inline *sinline *entity',
            ]);
        }

        if (isset($update['message']['message']) and $update['message']['message'] === '*ping') {
            $this->messages->sendMessage([
                    'peer' => $update,
                    'message' => 'pong',
            ]);
        }

        if (isset($update['message']['message']) and isset($update['message']['id']) and $update['message']['message'] === '*info') {
            if (!isset($update['message']['reply_to_msg_id'])) {
                $this->messages->sendMessage(['peer' => $update, 'message' => 'No reply.']);
            } else {
                if (isset($update['_']) and ($update['_'] === 'updateNewChannelMessage')) {
                    $dump = yield $this->channels->getMessages([
                            'channel' => $update,
                            'id' => [
                                    [
                                            '_' => 'inputMessageID',
                                            'id' => $update['message']['reply_to_msg_id']
                                    ]
                            ],
                    ]);
                } else {
                    $dump = yield $this->messages->getMessages([
                            'id' => [
                                    [
                                            '_' => 'inputMessageID',
                                            'id' => $update['message']['reply_to_msg_id']
                                    ]
                            ],
                    ]);
                }
                if (!empty($dump['users'])) {
                    foreach ($dump['users'] as $i => $user) {
                        unset($dump['users'][$i]['phone']);
                    }
                }
                unset($dump['users']['phone']);
                $res = json_encode($dump, JSON_PRETTY_PRINT);
                if ($res == '') {
                    $res = var_export($dump, true);
                }
                $this->messages->sendMessage([
                        'peer' => $update,
                        'message' => $res,
                        'reply_to_msg_id' => $update['message']['id'],
                        'entities' => [
                                [
                                        '_' => 'messageEntityPre',
                                        'offset' => 0,
                                        'length' => mb_strlen($res),
                                        'language' => 'json'
                                ]
                        ]
                ]);

            }
        }

        if (isset($update['message']['message']) and $update['message']['message'] === '*dump') {
            if (!isset($update['message']['reply_to_msg_id'])) {
                $this->messages->sendMessage(['peer' => $update, 'message' => 'No reply.']);
            } else {
                if (isset($update['_']) and ($update['_'] === 'updateNewChannelMessage')) {
                    $dump = yield $this->channels->getMessages([
                            'channel' => $update,
                            'id' => [
                                    [
                                            '_' => 'inputMessageID',
                                            'id' => $update['message']['reply_to_msg_id']
                                    ]
                            ],
                    ]);
                } else {
                    $dump = yield $this->messages->getMessages([
                            'id' => [
                                    [
                                            '_' => 'inputMessageID',
                                            'id' => $update['message']['reply_to_msg_id']
                                    ]
                            ],
                    ]);
                }
                if (!isset($dump['messages'][0]['reply_markup'])) {
                    $this->messages->sendMessage(['peer' => $update, 'message' => 'No buttons.']);
                } else {
                    $buttons = $dump['messages'][0]['reply_markup'];
                    $str = '';
                    foreach ($buttons['rows'] as $row) {
                        foreach ($row['buttons'] as $button) {
                            if (isset($button['text'])) {
                                $str .= 'Text: <code>' . htmlspecialchars($button['text']) . '</code>' . PHP_EOL;
                            }
                            if (isset($button['data'])) {
                                $str .= 'Data: <code>' . htmlspecialchars($button['data']) . '</code>' . PHP_EOL;
                            }
                            if (isset($button['query'])) {
                                $str .= 'Query: <code>' . htmlspecialchars($button['query']) . '</code>' . PHP_EOL;
                            }
                            if (isset($button['url'])) {
                                $str .= 'Url: <code>' . htmlspecialchars($button['url']) . '</code>' . PHP_EOL;
                            }
                            $str .= PHP_EOL;
                        }
                    }
                    $this->messages->sendMessage([
                            'peer' => $update,
                            'message' => $str,
                            'reply_to_msg_id' => $update['message']['reply_to_msg_id'],
                            'parse_mode' => 'HTML'
                    ]);
                }

            }
        }

        if (isset($update['message']['message']) and (stripos($update['message']['message'], '*inline') === 0)) {
            $ex = explode(' ', $update['message']['message'], 3);
            if (!isset($ex[2])) {
                $ex[2] = '';
            }
            if (isset($ex[1])) {
                $messages_BotResults = yield $this->messages->getInlineBotResults([
                        'bot' => $ex[1],
                        'peer' => $this->get_self()['id'],
                        'query' => $ex[2],
                        'offset' => '',
                ]);
                if (isset($messages_BotResults)) {
                    $inline = $messages_BotResults['results'];
                    $str = 'Inline:' . PHP_EOL . PHP_EOL;
                    foreach ($inline as $arr) {
                        foreach ($arr as $key => $value) {
                            if ($key == 'title') {
                                $str .= $value . ': ' . PHP_EOL;
                            }
                            if ($key == 'thumb') {
                                $str .= $value['url'] . PHP_EOL;
                            }
                        }
                    }
                    $this->messages->sendMessage([
                            'peer' => $update,
                            'message' => $str,
                            'reply_to_msg_id' => $update['message']['id'],
                    ]);
                }
            }
        }

        if (isset($update['message']['message']) and (stripos($update['message']['message'], '*sinline') === 0)) {
            $ex = explode(' ', $update['message']['message'], 3);
            if (!isset($ex[2])) {
                $ex[2] = '';
            }
            if (isset($ex[1])) {
                $messages_BotResults = yield $this->messages->getInlineBotResults([
                        'bot' => $ex[1],
                        'peer' => $this->get_self()['id'],
                        'query' => $ex[2],
                        'offset' => '',
                ]);
                if (isset($messages_BotResults)) {
                    $str = json_encode($messages_BotResults, JSON_PRETTY_PRINT);
                    $this->messages->sendMessage([
                            'peer' => $update,
                            'message' => $str,
                            'reply_to_msg_id' => $update['message']['id'],
                            'entities' => [
                                    [
                                            '_' => 'messageEntityPre',
                                            'offset' => 0,
                                            'length' => mb_strlen($str),
                                            'language' => 'json'
                                    ]
                            ]
                    ]);
                }
            }
        }

        if (isset($update['message']['message']) and $update['message']['message'] === '*entity') {
            if (!isset($update['message']['reply_to_msg_id'])) {
                $this->messages->sendMessage(['peer' => $update, 'message' => 'No reply.']);
            } else {
                if (isset($update['_']) and ($update['_'] === 'updateNewChannelMessage')) {
                    $dump = yield $this->channels->getMessages([
                            'channel' => $$update,
                            'id' => [
                                    [
                                            '_' => 'inputMessageID',
                                            'id' => $update['message']['reply_to_msg_id']
                                    ]
                            ],
                    ]);
                } else {
                    $dump = yield $this->messages->getMessages([
                            'id' => [
                                    [
                                            '_' => 'inputMessageID',
                                            'id' => $update['message']['reply_to_msg_id']
                                    ]
                            ],
                    ]);
                }
                if (!empty($dump['users'])) {
                    foreach ($dump['users'] as $i => $user) {
                        unset($dump['users'][$i]['phone']);
                    }
                }
                unset($dump['users']['phone']);

                if (isset($dump['messages'][0]['entities'])) {
                    $res = (string)count($dump['messages'][0]['entities']);
                } else {
                    $res = '0';
                }

                $this->messages->sendMessage([
                        'peer' => $update,
                        'message' => $res,
                        'reply_to_msg_id' => $update['message']['id']
                ]);
            }
        }

    }
}

$settings = [
        'app_info' => [
                'api_id' => MyEventHandler::API_ID,
                'api_hash' => MyEventHandler::API_HASH
        ],
        'logger' => [
                'logger_level' => Logger::VERBOSE,
                'logger' => Logger::FILE_LOGGER
        ],
        'serialization' => [
                'serialization_interval' => 30,
        ],
];

$MadelineProto = new \danog\MadelineProto\API(MyEventHandler::SESSION_NAME . '.madeline', $settings);

$MadelineProto->startAndLoop(MyEventHandler::class);
