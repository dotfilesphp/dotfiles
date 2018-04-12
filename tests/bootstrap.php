<?php

declare(strict_types=1);

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/../vendor/autoload.php';
define('DOTFILES_PHAR_MODE', false);
\Dotfiles\Core\Util\Toolkit::loadDotEnv();

$home = sys_get_temp_dir().'/dotfiles/home';
if (!is_dir($home)) {
    mkdir($home, 0755, true);
}
putenv("HOME=${home}");
