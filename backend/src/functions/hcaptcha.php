<?php

function verify_hcaptcha($token) {
    $secret = "SECRET_KEY";

    $url = "https://hcaptcha.com/siteverify";

    $data = [
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        return false;
    }

    $json = json_decode($result, true);

    return $json['success'] ?? false;
}
