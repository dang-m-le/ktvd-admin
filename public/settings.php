<?php
require_once("config.php");
require_once("jwt.php");
require_once("utils.php");

$req = json_decode(file_get_contents("php://input"), true);
$op = $req['op'];

$cred = get_credential(true);

//if (!$cred) {
//    js_exit(array('status'=>'unauthorized', 'require_privilege'=>'basic',
//                  'message'=>"Please sign-in."));
//}

if ($op == "get") {
    $dbc = school_db();
    $stm = $dbc->prepare("select * from settings");
    $stm->execute();
    $settings = array();
    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['attr']] = $row['value'];
    }
    $stm->closeCursor();
    js_exit(array('status'=>'okay', 'settings'=>$settings));
}

if (!accessible($cred, 'admin')) {
    js_exit(array('status'=>'unauthorized', 'require_privilege'=>'admin',
                  'message'=>"Please sign-in with \u{00ab}admin\u{00bb} privilege."));
}

if ($op == "save") {
    $dbc = school_db();
    $stm = $dbc->prepare("insert into settings set attr = ?, value = ? on duplicate key update value = values(value)");
    foreach ($req['settings'] as $attr=>$value) {
        $stm->execute(array($attr, $value));
    }
    $stm->closeCursor();
    js_exit(array('status'=>'okay', 'saved'=>true, 'message'=>'Saved School Settings.'));
}

js_exit(array('status'=>'error', 'message'=>"Invalid settings operation: $op."));
?>
