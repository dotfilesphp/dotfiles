<?php

require __DIR__.'/../vendor/autoload.php';

$home = sys_get_temp_dir().'/dotfiles/home';
if(!is_dir($home)){
    mkdir($home,0755,true);
}
putenv("HOME=${home}");
