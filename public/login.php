<?php
require_once("config.php");
require_once("jwt.php");

$redirect = false;

if ($_SERVER["CONTENT_TYPE"] == 'application/x-www-form-urlencoded') {
    $redirect = true;
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $persistent = $_POST['persistent'] == 'on';
}
else {
    $input = file_get_contents('php://input');
    error_log($input);
    $req = json_decode($input, TRUE);
    $username = trim("$req[username]");
    $password = trim("$req[password]");
    $persistent = $req["persistent"] === TRUE;
}

$resp = array("username"=>$username, "status"=>"error", "message"=>"Hey, fill this out!");

try {
  if ($username == "" || $password == "") {
    throw new Exception("Empty username/password!");
  }

  $dbh = school_db();
  if (!$dbh) {
    throw new Exception("Website error: Cannot connect to database");
  }

  $dno = intval($username);
  $rec = FALSE;
  $stm = $dbh->prepare("select * from users where username = ?");
  if (!$stm) {
      error_log(array_pop($dbc->errorInfo()));
      throw new Exception("Website error: Invalid query.");
  }
  
  if (!$stm->execute([$username])) {
      error_log(array_pop($stm->errorInfo()));
      throw new Exception("Website error: Bad query.");
  }
  
  $rec = $stm->fetch(PDO::FETCH_ASSOC);
  if (!$rec) {
      throw new Exception("Unregistered user: $username!");
  }

  if ($rec['deactivated']) {
      throw new Exception("Deactivated user: $username!");
  }

  $stm->closeCursor();
  
  if ($rec['deactivated']) {
      throw new Exception("Decommisioned user: $username!");
  }
  
  if (!bcrypt_check($password, $rec['password'])) {
      throw new Exception("Invalid password for user: $username!");
  }

  $username = $rec['username'];
  $fullname = "$rec[fullname]";
  $resp['status'] = "okay";
  $resp['username'] = $username;
  $resp['fullname'] = $fullname;
  $resp['access'] = $rec['access'];
  $resp['message'] = "Hello $fullname.";
  
  renew_payload($username, $fullname, $rec['role'], $rec['access'], $persistent);
}
catch (Exception $x) {
  $resp['message'] = $x->getMessage();
}

if ($redirect) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
else {
    echo json_encode($resp);
}
?>
