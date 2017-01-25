<?php
function debug($arr) {
    echo '<pre>' . print_r($arr, true) . '</pre>';
}

function mySort($f1,$f2)
{
    if($f1->id < $f2->id) return -1;
    elseif($f1->id > $f2->id) return 1;
    else return 0;
}