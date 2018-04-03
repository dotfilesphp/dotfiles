<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\Application as BaseApplication;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Emitter;

class Application extends BaseApplication
{
    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->setEmitter(new Emitter());
        $this->setConfig(new Config());
    }
}
