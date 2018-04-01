<?php

require __DIR__.'/../vendor/autoload.php';

use Dotfiles\Core\Util\Config;

$config = Config::create();
$home = $config->getTempDir('test/home');
putenv("HOME=${home}");
