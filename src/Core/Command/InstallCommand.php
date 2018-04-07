<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Command;

use Dotfiles\Core\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Event\Dispatcher;

class InstallCommand extends Command implements CommandInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Config
     */
    private $config;

    public function __construct(?string $name = null, Dispatcher $dispatcher, Config $config)
    {
        parent::__construct($name);
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }

    public function configure()
    {
        $this->setName('install');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Begin installing <comment>dotfiles</comment>');
        $config = $this->config;
        if(!is_dir($dir = $config->get('dotfiles.bin_dir'))){
            mkdir($dir,0755,true);
        }
        if(!is_dir($dir = $config->get('dotfiles.vendor_dir'))){
            mkdir($dir,0755,true);
        }
        $event = new InstallEvent();
        $this->dispatcher->dispatch(InstallEvent::NAME,$event);
    }
}
