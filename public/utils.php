<?php

function js_exit($v, $exit_code=0)
{
    echo json_encode($v);
    exit($exit_code);
}

function alt() 
{
    $val = null;
    for($i = 0; $i < func_num_args(); $i++){
        $val = func_get_arg($i);
        if ($val) {
            return $val;
        }
    }
    return $val; // return the last arg
}

function lowkey($p)
{
    if (gettype($p) == 'array') {
        $a = array();
        foreach($p as $k=>$v) {
            $a[strtolower($k)] = $v;
        }
        return $a;
    }
    else {
        return $p;
    }
}

function phone_number($a, $prefix='') 
{
    if (!$a)
        return "";
    return $prefix." ".substr($a,0,3)."-".substr($a,3,3)."-".substr($a,6);
}

function student_fullname($a) 
{
    return $a['saintname']." ".$a['lastname']." ".$a['middlename']." ".$a['firstname']
                          .($a['nickname'] ? " (".$a['nickname'].")" : "");
}

function desc_date($a) 
{
    if (!$a) {
        return "";
    }
    $t = strtotime($a);
    return date("m-d-Y", $t);
}

function get_query_columns($q) {
    $v = array();
    $ncols = $q->columnCount();
    for ($i=0; $i<$ncols; ++$i) {
        $meta = $q->getColumnMeta($i);
        $v[] = strtolower($meta['name']);
    }    
    return $v;
}

function make_map($keys, $val='') {
    $v = array();
    foreach($keys as $key) {
        $v[$key] = $val;
    }
    return $v;
}

?>
