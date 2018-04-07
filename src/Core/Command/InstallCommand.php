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
use Dotfiles\Core\Util\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Event\Dispatcher;
use Symfony\Component\Finder\Finder;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ?string $name = null,
        Dispatcher $dispatcher,
        Config $config,
        LoggerInterface $logger
    )
    {
        parent::__construct($name);
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function configure()
    {
        $this->setName('install');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Begin installing <comment>dotfiles</comment>');
        $config = $this->config;
        if (!is_dir($dir = $config->get('dotfiles.bin_dir'))) {
            mkdir($dir, 0755, true);
        }
        if (!is_dir($dir = $config->get('dotfiles.vendor_dir'))) {
            mkdir($dir, 0755, true);
        }
        $event = new InstallEvent();
        $this->dispatcher->dispatch(InstallEvent::NAME, $event);
        $this->doCopyTemplates($output);
        if(!is_null($machineName = $config->get('dotfiles.machine_name'))){
            $this->doProcessMachine($machineName);
        }
    }

    private function doProcessMachine($machineName,$overwrite=false)
    {
        $baseDir = $this->config->get('dotfiles.base_dir');
        $templateDir = $baseDir.'/machines/'.$machineName.'/templates';
        if(is_dir($templateDir)){
            $this->doProcessTemplates($templateDir,$overwrite);
        }
    }

    private function doCopyTemplates(OutputInterface $output)
    {
        $config = $this->config;
        $baseDir = $config->get('dotfiles.base_dir');
        $output->writeln("Copy files from <comment>$baseDir</comment>");
        $this->doProcessTemplates($baseDir . '/defaults/templates');
    }

    private function doProcessTemplates($templateDir,$overwrite = false)
    {
        $targetDir = getenv('HOME');
        if (!is_dir($templateDir)) {
            $this->debug("Template directory <comment>$templateDir</comment> not found");
            return;
        }
        $this->debug("copy files from <comment>$templateDir</comment>");

        $finder = Finder::create()
            ->in($templateDir)
            ->ignoreVCS(true)
            ->files()
        ;
        $fs = new Filesystem();
        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach($finder->files() as $file){
            $source = $file->getRealPath();
            $target = $targetDir.DIRECTORY_SEPARATOR.'.'.$file->getRelativePathname();
            $fs->copy($source,$target,['overwriteNewerFiles' => $overwrite]);
        }
    }

    private function doPatch($targetDir,$patchDir)
    {
        $fs = new Filesystem();
        $targetDir = getenv('HOME');
        
    }

    private function debug($message,$context = array())
    {
        $this->logger->debug($message,$context);
    }
}
