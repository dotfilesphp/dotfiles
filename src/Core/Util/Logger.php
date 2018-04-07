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

namespace Dotfiles\Core\Util;

use Monolog\Logger as BaseLogger;
use Psr\Log\LoggerInterface;

class Logger extends BaseLogger implements LoggerInterface
{
    public function __construct($handlers = array(), $processors = array())
    {
        parent::__construct('dotfiles');
    }
}
