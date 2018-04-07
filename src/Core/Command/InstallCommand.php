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
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Dotfiles\Core\Util\Toolkit;
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

    /**
     * @var array
     */
    private $patches = array();

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
        $this->doProcessSection($output, 'defaults');
        if(!is_null($machineName = $config->get('dotfiles.machine_name'))){
            $this->doProcessSection($output,'machines/'.$machineName);
        }
        $this->doApplyPatch();
    }

    private function doProcessSection(OutputInterface $output, $section)
    {
        $config = $this->config;
        $baseDir = $config->get('dotfiles.base_dir');
        $output->writeln("Processing <comment>$section</comment> section");
        $this->doProcessTemplates($baseDir . '/'.$section.'/templates');
        $this->doProcessPatch($baseDir.'/'.$section.'/patch');
        $this->doProcessInstallHook($baseDir.'/'.$section.'/custom');
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
            $this->backup($target);
            $fs->copy($source,$target,['overwriteNewerFiles' => $overwrite]);
        }
    }

    private function doProcessPatch($patchDir)
    {
        if(!is_dir($patchDir)){
            return;
        }
        $targetDir = getenv('HOME');
        $finder = Finder::create()
            ->in($patchDir)
        ;
        foreach($finder->files() as $file){
            $target = getenv('HOME').DIRECTORY_SEPARATOR.'.'.$file->getRelativePathName();
            $patch = file_get_contents($file);
            if(!isset($this->patches[$target])){
                $this->patches[$target] = [];
            }
            $this->patches[$target][] = $patch;
        }
    }

    private function doApplyPatch()
    {
        $fs = new Filesystem();
        foreach($this->patches as $target => $patches)
        {
            $patchContents = implode("\n", $patches);
            $fs->patch($target,$patchContents);
        }
    }

    private function doProcessInstallHook($hookDir)
    {

    }

    private function backup($file)
    {
        $relativePathName = str_replace(getenv('HOME').DIRECTORY_SEPARATOR,'',$file);
        $fs = new Filesystem();
        $backupDir = $this->config->get('dotfiles.backup_dir');
        $target = $backupDir.DIRECTORY_SEPARATOR.$relativePathName;
        Toolkit::ensureDir($target);
        if(is_file($file) && !is_file($target)){
            $fs->copy($file,$target,['overwriteNewerFiles' => true]);
        }
    }

    private function debug($message,$context = array())
    {
        $this->logger->debug("install: ".$message,$context);
    }
}
