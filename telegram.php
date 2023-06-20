<?php 
    /* для отправки текстовых сообщений */
    function TG_sendMessage($getQuery) {
        $ch = curl_init("https://api.telegram.org/bot". TG_TOKEN ."/sendMessage?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    /* для отправки изображений */
    function TG_sendPhoto($arrayQuery) {
        $ch = curl_init('https://api.telegram.org/bot'. TG_TOKEN .'/sendPhoto');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    /* для получения данных о файле */
    function TG_getFile($arrayQuery) {
        $ch = curl_init("https://api.telegram.org/bot". TG_TOKEN ."/getFile?" . http_build_query($arrayQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }