<?php

function get_first_pic($str){
    $pic = json_decode($str)[0];
    return $pic;
}