<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toni\Dotfiles;

use Symfony\Component\Console\Application as BaseApplication;
use Toni\Dotfiles\Command\InstallCommand;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('dotfiles', '1.0.0');
        $this->buildCommands();
    }

    public function buildCommands()
    {
        $this->addCommands([
            new InstallCommand()
        ]);
    }
}