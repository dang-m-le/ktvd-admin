<?php
require_once("config.php");
require_once("jwt.php");
require_once("utils.php");

$req = json_decode(file_get_contents("php://input"), true);
$op = $req['op'];
$cred = get_credential(true);

if (!$cred || !accessible($cred, 'admin')) {
    js_exit(array('status'=>'unauthorized', 'require_privilege'=>'basic',
                  'message'=>"Please sign-in with \u{00ab}admin\u{00bb} privilege."));
}

if ($op == "list") {
    $dbc = school_db();
    $stm = $dbc->prepare("select * from student join schedule on student.id = schedule.id");
    $stm->execute();
    $students = array();
    while ($row = lowkey($stm->fetch(PDO::FETCH_ASSOC))) {
        $row['baptized'] = $row['baptized'] == 'Y';
        $row['confession'] = $row['confession'] == 'Y';
        $row['confirmed'] = $row['confirmed'] == 'Y';
        $row['communion'] = $row['communion'] == 'Y';
        $students[] = $row;
    }
    $stm->closeCursor();
    js_exit(array('status'=>'okay', 'students'=>$students));
}


if (!accessible($cred, 'admin')) {
    js_exit(array('status'=>'unauthorized', 'require_privilege'=>'admin',
                  'message'=>"Please sign-in with \u{00ab}admin\u{00bb} privilege."));
}

js_exit(array('status'=>'eror', 'message'=>"Invalid settings operation: \u{00ab}$op\u{00bb}."));
?>
