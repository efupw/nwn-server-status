<?php

/**
 * Queries the status of the NWN server at the given address.
 *
 * If no NWN server responds to the query, it is assumed to be offline,
 * even if no server has ever existed in the first place.
 *
 * The response is undefined unless the arguments are valid.
 *
 * Given a valid query, the return value will be a map
 * with the following keys:
 *
 *  - connected: bool
 *      Always. True only when the server responded with a non-empty message.
 *  - error: string
 *      When connected === false. An error message hinting at the problem.
 *      Not meant for user consumption.
 *
 * The following keys are only defined when connected === true.
 *
 * - server_name: string
 *      The server's name as reported in the lobby.
 * - current_players: int
 *      The current player count.
 * - max_players: int
 *      The max player count.
 * - pvp: enum
 *      The PvP setting. One of NONE, PARTY, FULL.
 * - description:string
 *      The server's description. For unknown reasons, this is often cut short.
 *      Suspected reasons include the format of the UDP packet
 *      sent to the server and the socket-read terminating prematurely.
 *
 * @param $hostname string A fully qualified hostname, minus the protocol.
 * @param $port int|string The port the NWN server is listening to.
 * @return array A map of server status values, as per above.
 */
function nwn_server_status($hostname, $port) {
    $fp = fsockopen("udp://{$hostname}", $port, $errno, $errstr, 2);
    if (!$fp) {
        return array(
            'connected' => false,
            'error' => "{$errno}: {$errstr}",
        );
    }

    $gamespy_2_details = "\xFE\xFD\x00\x43\x4F\x52\x59\xFF\x00\x00";

    $write = fwrite($fp, $gamespy_2_details);
    $timeout = stream_set_timeout($fp, 2);
    $response = fread($fp, 5000);
    $close = fclose($fp);

    if (!$write || !$timeout || !$close) {
        return array(
            'connected' => false,
            'error' => "Failed to query server {$hostname}:{$port}",
        );
    }

    if (!$response || !($res = explode("\x00", $response))) {
        return array(
            'connected' => false,
            'error' => "Null or empty response from server {$hostname}:{$port}"
                . " ('{$response}')",
        );
    }

    $name = 2;
    $current_players = 6;
    $max_players = 8;
    $pvp = 26;
    $description = 34;

    return array(
        'connected'     => true,
        'server_name'   => $res[$name],
        'current_players' => $res[$current_players],
        'max_players'   => $res[$max_players],
        'pvp'           => $res[$pvp],
        'description'   => $res[$description],
    );
}
