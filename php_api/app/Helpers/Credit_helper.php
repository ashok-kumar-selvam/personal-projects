<?php

function get_credits_dates() {
return (object) [
'now' => time(),
'month_start' => strtotime(' first day of this month midnight'),
'month_end' => strtotime(' -1 second first day of next month midnight'),
'week_start' => strtotime(' this week midnight'),
'week_end' => strtotime(' -1 second next week midnight'),
'year_start' => strtotime(' first day of january this year midnight'),
'year_end' => strtotime(' -1 second first day of january next year midnight'),
'one_year' => strtotime(' -1 second next year'),
];
}

function get_remaining_credits($user_id) {
$db = db_connect();
$times = get_credits_dates();
$start = $times->month_start;
$end = $times->month_end;



$total_query = $db->table('credits_earned')->where('user_id', $user_id)->where('from <=', $start)->where('to >=', $end)->selectSum('credit', 'total_credits')->get();
$total_result = $total_query->getRow();
$spent_credits = $db->table('credits_spent')->where('user_id', $user_id)->where('date >=', $start)->where('date <=', $end)->countAllResults();
return $total_result->total_credits-$spent_credits;

}


function sendTelegram($messaggio) {
$chatID = '1009469840';
$token = 'bot1479668212:AAHn9K7ds0CvoSKJt2jsr6mU1UU5KKEnpSQ';
    $url = "https://api.telegram.org/" . $token . "/sendMessage?chat_id=" . $chatID;
    $url = $url . "&text=" . urlencode($messaggio);
    $ch = curl_init();
    $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

