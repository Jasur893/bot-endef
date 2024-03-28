<?php

//echo 'hello port 80';

set_time_limit(0);
ob_start();

const TG_TOKEN = "6206608323:AAG9Zy_mKNccA5IX0bCACV3ziyGgQETUi1M";
const TG_USER_ID = "1078608772";

function telegramMethod($method, $datas = [])
{
    $url = "https://api.telegram.org/bot" .TG_TOKEN . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);

    $res = curl_exec($ch);
    curl_close($ch);

    if (curl_error($ch)){
        var_dump(curl_error($ch));
    } else {
        return json_decode($res, true);
    }
}

function sendMessage($chat_id, $text): void
{
    telegramMethod('sendMessage', [
        'chat_id' =>  $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ]);
}

function sendRequest($value): bool|string
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => "https://api.urbandictionary.com/v0/define?term=$value"
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    return $response;
}


$update = file_get_contents('php://input');
file_put_contents('data.json', $update);
$update = json_decode($update);
$chat_id = $update->message->from->id;
$text = trim($update->message->text);
$first_name = $update->message->from->first_name;

if (str_word_count($text) > 1 and $text[0] == '/') {
    $text_arr = explode(" ", $text);
    $action = $text_arr[0];
    $value = $text_arr[1];

    switch ($action) {
        case '/define':
            $response = sendRequest($value);
            $array = json_decode($response, true);

            $count = 0;
            $def = "";
            foreach ($array as $item => $value) {
                foreach ($value as $data => $val) {
                    $count++;
                    $def .= "<strong>Definition $count : </strong>" . $val['definition'] . "\n<i>Example $count : " . $val['example'] . "</i>\n\n";
                    if ($count == 3) {
                        break;
                    }
                }
            }


            sendMessage($chat_id, $def);
            break;

        default:
            break;
    }
} else {
    switch ($text) {
        case '/start':
            sendMessage($chat_id, 'Welcome' . " " . $first_name);
            break;

        case '/about':
            sendMessage($chat_id, 'Project name: telegram bot demo');
            break;

        case '/help':
            sendMessage($chat_id, "after /define command write your word to find its meaning \n Example: /define apple");
            break;

        case '/define':
            sendMessage($chat_id, 'After the /define command you have to write at least one word');
            break;

        default:
            break;
    }

}




