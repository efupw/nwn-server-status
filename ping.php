<?php
require_once 'config.local.php';

function nwn_server_status($hostname, $port) {
    $fp = fsockopen("udp://{$hostname}", $port, $errno, $errstr, 2);
    if (!$fp) {
        return "Offline";
    }

    $send = "\xFE\xFD\x00\xE9\x49\x04\x05\x14\x01\x0B\x01\x05\x08\x0A\x33\x34\x35\x13\x04\x36\x37\x38\x39\x14\x3A\x3B\x3C\x3D\x00\x00";

    fwrite($fp, $send);
    stream_set_timeout($fp, 2);
    $fresponse = fread($fp, 5000);
    fclose($fp);
    if (!$fresponse) {
        return "Offline";
    }

    $name = 3;
    $current_players = 5;
    $max_players = 6;
    $description = 15;
    $res = explode("\x00", $fresponse);
    return "Online ("
        . $res[$current_players]
        . " / "
        . $res[$max_players]
        . " players)";
}

$status = nwn_server_status($hostname, $port);
echo $status;