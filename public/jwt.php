<?php
require_once("config.php");

function bcrypt_hash($password, $work_factor = 12)
{
  /* Marco Arment <me@marco.org>. */
  $work_factor = max(min($work_factor,31), 4);
  $salt = '$2y$' 
    . str_pad($work_factor, 2, '0', STR_PAD_LEFT) . '$' 
    . substr(strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'), 0, 22)
    ;
  return crypt($password, $salt);
}

function bcrypt_check($password, $stored_hash)
{
  return hash_equals($stored_hash, crypt($password, $stored_hash));
}

function get_private_key() {
  global $jwt_private_cert;
  return openssl_pkey_get_private(file_get_contents($jwt_private_cert));
}

function get_public_key() {
  global $jwt_public_cert;
  return openssl_pkey_get_public(file_get_contents($jwt_public_cert));
}

function base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function jwt_rs256_create($payload)
{
  $header = array('alg'=>'RS256', 'typ'=>'JWT');

  $segments = array();
  $segments[] = base64url_encode(json_encode($header));
  $segments[] = base64url_encode(json_encode($payload));

  $digest = hash_hmac('sha256', implode('.', $segments), "", true);
  if (!openssl_private_encrypt($digest, $cypher, get_private_key())) {
    return FALSE;
  }
  $signature = base64url_encode($cypher);
  $segments[] = $signature;

  $token = implode('.', $segments);
  return $token;
}

function jwt_rs256_verify($token)
{
  $segments = explode('.', $token);
  if (count($segments) != 3) {
    return FALSE;
  }
  $signature = array_pop($segments);
  $cypher = base64url_decode($signature);
  openssl_public_decrypt($cypher, $result, get_public_key());  
  $verify = base64url_encode($result);
  $digest = base64url_encode(hash_hmac('sha256', implode('.', $segments), "", true));
  if ($verify !== $digest) {
    return FALSE;
  }

  return array(
    'header' => json_decode(base64url_decode($segments[0]), true),
    'payload' => json_decode(base64url_decode($segments[1]), true),
    'signature' => $signature);
}


function a2perm($role) {
  if ($role == 'admin'|| $role == 'oper')
    return POSIX_W_OK|POSIX_R_OK|POSIX_X_OK;
  return 0;
}

function a2mode($access_mod) {
    return 0;
}

function renew_payload($username, $fullname, $role, $access, $persistent)
{
    global $TOKEN_EXPIRATION, $TOKEN_COOKIE;
    $iat = time();
    $exp = $iat + $TOKEN_EXPIRATION;
    $payload = array('sub'=>'ktvd',
                     'iat'=>$iat,
                     'exp'=>$exp,
                     'username'=>$username,
                     'fullname'=>$fullname,
                     'role'=>$role,
                     'access'=>"$access",
                     'persist'=>boolval($persistent));
  
    $token = jwt_rs256_create($payload);
    setrawcookie($TOKEN_COOKIE, $token, (1 || $persistent) ? $exp : 0, "", "", FALSE, TRUE);
}

function update_payload($cred)
{
    renew_payload($cred['username'], $cred['fullname'], $cred['role'], $cred['access'],
                  $cred['persist']);
}

function get_payload($do_update=false) {
  global $TOKEN_COOKIE;
  $token = @$_COOKIE[$TOKEN_COOKIE];
  if (!$token) {
    return FALSE;
  }

  $jwt = jwt_rs256_verify($token);
  if ($jwt === FALSE) {
    return FALSE;
  }

  if ($do_update) {
      update_payload($jwt['payload']);
  }

  return $jwt['payload'];
}

function get_credential($do_update) {
    $cred = get_payload();
    if ($cred && $cred['exp'] > time()) {
        renew_payload($cred['username'], $cred['fullname'], $cred['role'], $cred['access'],
                      $cred['persist']);
        return $cred;
    }
    return null;
}


function accessible($credential, $access)
{
    if (!$credential || !isset($credential['access'])) {
        return false;
    }
    return in_array($access, explode(',', @$credential['access']));
}

?>
