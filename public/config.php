<?php

$TOKEN_EXPIRATION = 365*24*60*60;
$TOKEN_COOKIE = "KTVD";
$jwt_private_cert = "../certs/ktvd.key";
$jwt_public_cert = "../certs/ktvd.crt";

function school_db() {
    return new PDO("mysql:host=localhost; dbname=ktvdenve_school; charset=utf8",
        "ktvdenve_school", "Cha(mChi?");
}

?>
