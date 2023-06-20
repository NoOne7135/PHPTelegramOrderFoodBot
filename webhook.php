<?php

$getQuery = array(
    "url" => URL_WEBHOOK,
);
$ch = curl_init("https://api.telegram.org/bot". TG_TOKEN ."/setWebhook?" . http_build_query($getQuery));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$resultQuery = curl_exec($ch);
curl_close($ch);