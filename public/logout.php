<?php
require('jwt.php');

$resp = array("status"=>"error", "message"=>"Sorry, I don't remember who you are.");

$token = $_COOKIE[$TOKEN_COOKIE];
if (!$token) {
    ;
}
else if (($jwt = jwt_rs256_verify($token)) === FALSE) {
    $resp['message'] = 'Hey, bad token.';
    setrawcookie($TOKEN_COOKIE, "", time()-1, "", "", FALSE, TRUE);
}
else {
    $resp['status'] = 'okay';
    $resp['message'] = "Good bye ".$jwt['payload']['fullname'].". Godspeed!";
    setrawcookie($TOKEN_COOKIE, "", time()-1, "", "", FALSE, TRUE);
}

echo json_encode($resp);
?>
