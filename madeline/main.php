<?php
require_once '/app/lib/madeline/vendor/autoload.php';
require_once 'debugBot.php';

define('SESSION_NAME', 'user');
// userid
define('ADMINS', [1234, 5678]);
// https://core.telegram.org/api/obtaining_api_id
define('API_ID', 6);
define('API_HASH', 'eb06d4abfb49dc3eeb1aeb98ae0f581e');

// debugbot bot api token
define('DEBUG_TOKEN', '12345:AAbcdef12345');
// debugbot userid
define('DEBUG_ID', 1234);


/** @noinspection PhpUndefinedClassInspection */
class EventHandler extends \danog\MadelineProto\EventHandler
{
    private function getIdCustom($peer)
    {
        if (isset($peer['chat_id'])) {
            return -$peer['chat_id'];
        }
        if (isset($peer['user_id'])) {
            return $peer['user_id'];
        }
        if (isset($peer['channel_id'])) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $this->to_supergroup($peer['channel_id']);
        }

        return false;
    }

    public function __construct($MadelineProto)
    {
        parent::__construct($MadelineProto);
    }

    public function onAny($update)
    {
        //\danog\MadelineProto\Logger::log("Received an update of type " . $update['_']);
    }

    public function onLoop()
    {
        //\danog\MadelineProto\Logger::log("Working...");
    }

    public function onUpdateNewChannelMessage($update)
    {
        $this->onUpdateNewMessage($update);
    }

    public function onUpdateNewMessage($update)
    {
        /*if (isset($update['message']['out']) && $update['message']['out']) {
            return;
        }*/

        if ((!isset($update['message']['from_id'])) or (!in_array($update['message']['from_id'], ADMINS))) {
            return;
        }

        try {

            if (isset($update['message']['message']) and isset($update['message']['id']) and $update['message']['message'] === '*help') {
                $this->messages->sendMessage([
                        'peer' => $update,
                        'message' => 'Comandi: *help *ping *info *dump *inline *sinline *entity',
                ]);
            }

            if (isset($update['message']['message']) and isset($update['message']['id']) and $update['message']['message'] === '*ping') {
                $this->messages->sendMessage([
                        'peer' => $update,
                        'message' => 'pong',
                ]);
            }

            if (isset($update['message']['message']) and isset($update['message']['id']) and $update['message']['message'] === '*info') {
                if (!isset($update['message']['reply_to_msg_id'])) {
                    $this->messages->sendMessage(['peer' => $update, 'message' => 'No reply.']);
                } else {
                    if (isset($update['_']) and ($update['_'] === 'updateNewChannelMessage') and isset($update['message']['to_id'])) {
                        $dump = $this->channels->getMessages([
                                'channel' => $this->getIdCustom($update['message']['to_id']),
                                'id' => [
                                        [
                                                '_' => 'inputMessageID',
                                                'id' => $update['message']['reply_to_msg_id']
                                        ]
                                ],
                        ]);
                    } else {
                        $dump = $this->messages->getMessages([
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

            if (isset($update['message']['message']) and isset($update['message']['id']) and $update['message']['message'] === '*dump') {
                if (!isset($update['message']['reply_to_msg_id'])) {
                    $this->messages->sendMessage(['peer' => $update, 'message' => 'No reply.']);
                } else {
                    if (isset($update['_']) and ($update['_'] === 'updateNewChannelMessage') and isset($update['message']['to_id'])) {
                        $dump = $this->channels->getMessages([
                                'channel' => $this->getIdCustom($update['message']['to_id']),
                                'id' => [
                                        [
                                                '_' => 'inputMessageID',
                                                'id' => $update['message']['reply_to_msg_id']
                                        ]
                                ],
                        ]);
                    } else {
                        $dump = $this->messages->getMessages([
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
                    $messages_BotResults = $this->messages->getInlineBotResults([
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
                    $messages_BotResults = $this->messages->getInlineBotResults([
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

            if (isset($update['message']['message']) and isset($update['message']['id']) and $update['message']['message'] === '*entity') {
                if (!isset($update['message']['reply_to_msg_id'])) {
                    $this->messages->sendMessage(['peer' => $update, 'message' => 'No reply.']);
                } else {
                    if (isset($update['_']) and ($update['_'] === 'updateNewChannelMessage') and isset($update['message']['to_id'])) {
                        $dump = $this->channels->getMessages([
                                'channel' => $this->getIdCustom($update['message']['to_id']),
                                'id' => [
                                        [
                                                '_' => 'inputMessageID',
                                                'id' => $update['message']['reply_to_msg_id']
                                        ]
                                ],
                        ]);
                    } else {
                        $dump = $this->messages->getMessages([
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

        } catch (Exception $e) {
            debug($e->getCode() . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }


    }
}


$settings['app_info']['api_id'] = API_ID;
$settings['app_info']['api_hash'] = API_HASH;
$settings['logger']['logger_level'] = \danog\MadelineProto\Logger::NOTICE;
$settings['logger']['logger'] = \danog\MadelineProto\Logger::FILE_LOGGER;

$m = new \danog\MadelineProto\API(SESSION_NAME . '.madeline', $settings);

/** @noinspection PhpUndefinedMethodInspection */
$m->start();
/** @noinspection PhpUndefinedMethodInspection */
$m->setEventHandler('\EventHandler');
/** @noinspection PhpUndefinedMethodInspection */
$m->loop();
