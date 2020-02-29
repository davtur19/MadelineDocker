<?php

function curlRequest($method, $args = [])
{
    $ch = curl_init();
    curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_URL => 'https://api.telegram.org/bot' . DEBUG_TOKEN . '/' . $method,
            CURLOPT_POSTFIELDS => $args,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function sendMessage(
        $chat_id,
        $text,
        $parse_mode = null,
        $disable_web_page_preview = null,
        $disable_notification = null,
        $reply_to_message_id = null,
        $reply_markup = null
) {
    $args = [
            'chat_id' => $chat_id,
            'text' => $text,
    ];

    if (isset($parse_mode)) {
        $args['parse_mode'] = $parse_mode;
    }
    if (isset($disable_web_page_preview)) {
        $args['disable_web_page_preview'] = $disable_web_page_preview;
    }
    if (isset($disable_notification)) {
        $args['disable_notification'] = $disable_notification;
    }
    if (isset($reply_to_message_id)) {
        $args['reply_to_message_id'] = $reply_to_message_id;
    }
    if (isset($reply_markup)) {
        $reply_markup = json_encode($reply_markup);
        $args['reply_markup'] = $reply_markup;
    }

    return curlRequest('sendMessage', $args);

}

function debug(...$args)
{
    foreach ($args as $debug) {
        $str = var_export($debug, true);
        $array_str = str_split($str, 4050);

        foreach ($array_str as $value) {
            sendMessage(DEBUG_ID, 'LOG:' . PHP_EOL . $value);
        }
    }

    return true;
}
