<?php

function js_exit($v, $exit_code=0)
{
    echo json_encode($v);
    exit($exit_code);
}

?>
