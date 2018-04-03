<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Util;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as BaseLogger;

class Logger extends BaseLogger implements LoggerInterface
{
    public function __construct($handlers = array(), $processors = array())
    {
        if(!is_dir($dir = getcwd().'/var/log')){
            mkdir($dir,0755, true);
        }
        $handler = new StreamHandler($dir.DIRECTORY_SEPARATOR.'dotfiles.log');
        parent::__construct('dotfiles', [$handler], $processors);
    }

}
