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

namespace Dotfiles\Core\Helper\Tests;

use Dotfiles\Core\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestApplication extends BaseApplication
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var resource
     */
    private $stream;

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->setAutoExit(false);

        return parent::run($input, $output);
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
