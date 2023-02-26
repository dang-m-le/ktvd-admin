<?php
require_once("config.php");
require_once("jwt.php");

$resp = array("status"=>"error", "message"=>"Sorry, I don't remember who you are.");

$token = @$_COOKIE[$TOKEN_COOKIE];
if (!$token) {
    ;
}
else if (($jwt = jwt_rs256_verify($token)) === FALSE) {
    $resp['message'] = 'Hey, bad token.';
}
else if (!($payload = $jwt['payload'])) {
    $resp['message'] = 'Hey, what happened to the payload?';
}
else if (intval($payload['exp']) < time()) {
    $resp['message'] = 'Token expired.';
}
else {
    $resp['status'] = 'okay';
    $resp['username'] = $payload['username'];
    $resp['fullname'] = $payload['fullname'];
    $resp['access'] = $payload['access'];
    $resp['message'] = "Welcome back \u{00ab}".$payload['username']."\u{00bb} ". $payload['fullname'];
}

echo json_encode($resp);
?>
