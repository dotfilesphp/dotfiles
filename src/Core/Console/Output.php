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

namespace Dotfiles\Core\Console;

use Dotfiles\Core\Config\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class Output extends ConsoleOutput
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $options = \Symfony\Component\Console\Output\Output::OUTPUT_NORMAL): void
    {
        $messages = $this->stripFileName($messages);
        parent::write($messages, $newline, $options);
    }

    /**
     * Strip long filename to be shorter.
     *
     * @param string $messages
     *
     * @return string
     */
    private function stripFileName(string $messages): string
    {
        $config = $this->config;
        $dirs = array(
            $config->get('dotfiles.home_dir') => '$home_dir',
            $config->get('dotfiles.repo_dir') => '$repo_dir',
            $config->get('dotfiles.temp_dir') => '$temp_dir',
            $config->get('dotfiles.bin_dir') => '$dotfiles_dir/bin',
        );

        $messages = strtr($messages, $dirs);

        return $messages;
    }
}
