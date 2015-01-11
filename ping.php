<?php
require_once 'config.local.php';

$encoding = "latin1";
$fp = fsockopen("udp://{$hostname}", $port, $errno, $errstr, 2);
if (!$fp) {
  echo "Offline";
} else {
  fwrite($fp, "BNXI");
  stream_set_timeout($fp, 2);
  $fresponse = fread($fp, 48);
  fclose($fp);
  if ($fresponse) {
    echo("Online ("
        . ord(mb_substr($fresponse, 10, 1, $encoding))
        . " / "
        . ord(mb_substr($fresponse, 11, 1, $encoding))
        . " players)");
  } else {
    echo("Offline");
  }
}
