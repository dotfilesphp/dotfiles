<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Util;

use Dotfiles\Core\DI\Parameters;
use Symfony\Component\Console\Output\OutputInterface;

class BackupManifest
{
    /**
     * @var array
     */
    private $backups;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        Parameters $parameters
    ) {
    }

    public function add($file)
    {
    }
}
