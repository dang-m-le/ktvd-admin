<?php
require_once("config.php");
require_once("jwt.php");
require_once("utils.php");

$req = json_decode(file_get_contents("php://input"), true);
$op = $req['op'];
$cred = get_credential(true);

if (!$cred || !accessible($cred, 'admin')) {
    js_exit(array('status'=>'unauthorized', 'require_privilege'=>'admin',
                  'message'=>"Please sign-in with \u{00ab}admin\u{00bb} privilege."));
}

if ($op == "list") {
    $dbc = school_db();
    $stm = $dbc->prepare("select * from users");
    $stm->execute();
    $users = array();
    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        $row['common'] = ($row['common'] == 'Y');
        $row['deactivated'] = ($row['deactivated'] != null);
        unset($row['password']);
        $users[] = $row;
    }
    $stm->closeCursor();
    js_exit(array('status'=>'okay', 'users'=>$users));
}

if ($op == 'password') {
    $username = $req['username'];
    $oldpwd = $req['old_password'];
    $newpwd = $req['new_password'];
    
    $dbc = school_db();
    if ($oldpwd != '') {
        $stm = $dbc->prepare("select * from users where username = ?");
        if (!$stm->execute(array($username))) {
            js_exit(array('status'=>'error', 'message'=>"User database error"));
        }
        
        $rec = $stm->fetch(PDO::FETCH_ASSOC);
        $stm->closeCursor();
        if (!$rec) {
            js_exit(array('status'=>'error',
                          'message'=>"Unknown user \u{00ab}$req[username]\u{00bb}"));
        }
        
        if (!bcrypt_check($oldpwd, $rec['password'])) {
            js_exit(array('status'=>'error',
                          'message'=>"Old user \u{00ab}$req[username]\u{00bb} password mismatch."));
        }
    }

    $hash = bcrypt_hash($newpwd);
    $stm = $dbc->prepare("update users set password = ? where username = ?");
    if (!$stm->execute(array($hash, $username))) {
        js_exit(array('status'=>'error',
                      'message'=>"Failed to update \u{00ab}$username\u{00bb} password.",
                      'details'=>array_pop($stm->errorInfo())));
    }
    if ($stm->rowCount()) {
        js_exit(array('status'=>'okay',
                      'message'=>"Updated user \u{00ab}$username\u{00bb} password."));
    }
    
    js_exit(array('status'=>'error',
                  'message'=>"Failed to update user \u{00ab}$username\u{00bb} password."));
}

if (!accessible($cred, 'admin')) {
    js_exit(array('status'=>'unauthorized', 'require_privilege'=>'admin',
                  'message'=>"Please sign-in with \u{00ab}admin\u{00bb} privilege."));
}

if ($op == 'save') {
    $user = $req['user'];
    $orig = $req['orig'];
    $dbc = school_db();
    $ss = false;
    $error = "";
    if ($orig['username'] != '') {
        $stm = $dbc->prepare("update users set username=?, fullname=?, email=?, role=?, access=?, common=?, deactivated=? where username=?");
        $ss = $stm->execute(array(
            $user['username'],
            $user['fullname'],
            $user['email'],
            $user['role'],
            preg_replace('/[, ]+/', ',', $user['access']),
            $user['common'] ? 'Y' : 'N',
            $user['deactivated'] ? date('Y-m-d H:i:s') : null,
            $orig['username']
        ));
        if (!$ss) {
            $error = array_pop($stm->errorInfo());
        }
    }
    else {
        $stm = $dbc->prepare("insert into users(username,fullname,email,role,access,common,deactivated) values(?,?,?,?,?,?,?)");
        $ss = $stm->execute(array(
            $user['username'],
            $user['fullname'],
            $user['email'],
            $user['role'],
            preg_replace('/[, ]+/', ',', $user['access']),
            $user['common'] ? 'Y' : 'N',
            $user['deactivated'] ? date('Y-m-d H:i:s') : null
        ));
        if (!$ss) {
            $error = array_pop($stm->errorInfo());
        }
    }
    
    if ($ss) {
        js_exit(array('status'=>'okay',
                      'message'=>"Saved \u{00ab}$user[username]\u{00bb} $user[fullname]"));
    }
    
    js_exit(array('status'=>'error',
                  'message'=>"Failed to save \u{00ab}$user[username]\u{00bb} $user[fullname].",
                  'details'=>$error));
}

if ($op == 'remove') {
    $dbc = school_db();
    $stm = $dbc->prepare("delete from users where username = ?");
    $ss = $stm->execute(array($req['username']));
    if ($ss) {
        js_exit(array('status'=>'okay',
                      'message'=>"Removed \u{00ab}$req[username]\u{00bb} $req[fullname]."));
    }
    
    js_exit(array('status'=>'error',
                  'message'=>"Failed to remove \u{00ab}$req[username]\u{00bb} $req[fullname].",
                  'details'=>array_pop($stm->errorInfo())));
}

js_exit(array('status'=>'eror', 'message'=>"Invalid settings operation: \u{00ab}$op\u{00bb}."));
?>
