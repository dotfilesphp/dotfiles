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

namespace Dotfiles\Core\Command;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class InstallCommand extends Command implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    private $dryRun = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    private $overwriteNewFiles = false;

    /**
     * @var array
     */
    private $patches = array();

    public function __construct(
        ?string $name = null,
        Dispatcher $dispatcher,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($name);
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function configure(): void
    {
        $this->setName('install');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->dryRun = $input->hasOption('dry-run') ? $input->getOption('dry-run') : false;
        $this->getApplication()->get('backup')->execute($input, $output);

        $output->writeln('Begin installing <comment>dotfiles</comment>');
        $config = $this->config;
        $this->output = $output;

        Toolkit::ensureDir($config->get('dotfiles.bin_dir'));
        Toolkit::ensureDir($config->get('dotfiles.vendor_dir'));

        $this->processSection($output, 'defaults');
        if (null !== ($machineName = $config->get('dotfiles.machine_name'))) {
            $this->processSection($output, 'machines/'.$machineName);
        }

        $event = new InstallEvent();
        $event
            ->setDryRun($this->dryRun)
            ->setOverwriteNewFiles($this->overwriteNewFiles)
        ;
        $this->dispatcher->dispatch(InstallEvent::NAME, $event);
        $this->patches = array_merge($this->patches,$event->getPatches());

        $this->applyPatch();
    }

    private function applyPatch(): void
    {
        $fs = new Filesystem();
        foreach ($this->patches as $target => $patches) {
            $patchContents = implode("\n", $patches);
            if (!$this->dryRun) {
                $fs->patch($target, $patchContents);
            }
            $this->debug(
                sprintf(
                    'Patching file: <comment>%s</comment>',
                            Toolkit::stripPath($target)
                )
            );
        }
    }

    private function copy(string $origin, string $target): void
    {
        if (!$this->dryRun) {
            $fs = new Filesystem();
            $fs->copy($origin, $target, array('overwriteNewerFiles' => $this->overwriteNewFiles));
        }
        $this->debug(sprintf(
            'Copy files from <comment>%s</comment> to <comment>%s</comment>',
            Toolkit::stripPath($origin),
            Toolkit::stripPath($target)
        ));
    }

    private function debug($message, $context = array()): void
    {
        $this->logger->debug('install: '.$message, $context);
    }

    private function doProcessBin($binDir): void
    {
        if (!is_dir($binDir)) {
            return;
        }

        $finder = Finder::create()
            ->in($binDir)
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
        ;

        /* @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $target = $this->config->get('dotfiles.bin_dir').DIRECTORY_SEPARATOR.$file->getRelativePathName();
            $this->copy($file->getRealPath(), $target);
        }
    }

    private function doProcessInstallHook($hookDir): void
    {
        if (!is_dir($hookDir)) {
            return;
        }
        $finder = Finder::create()
            ->in($hookDir)
            ->name('install')
            ->name('install.sh')
        ;
        foreach ($finder->files() as $file) {
            $this->debug("executing <comment>$file</comment>");
            $cmd = $file;
            $process = new Process($cmd);
            $process->run(function ($type, $buffer): void {
                if (Process::ERR === $type) {
                    $this->output->writeln("Error: $buffer");
                } else {
                    $this->output->writeln($buffer);
                }
            });
        }
    }

    private function doProcessPatch($patchDir): void
    {
        if (!is_dir($patchDir)) {
            return;
        }
        $finder = Finder::create()
            ->in($patchDir)
        ;
        /* @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relativePathName = $this->normalizePathName($file->getRelativePathName());
            $target = $this->config->get('dotfiles.home_dir').DIRECTORY_SEPARATOR.$relativePathName;
            $patch = file_get_contents($file->getRealPath());
            if (!isset($this->patches[$target])) {
                $this->patches[$target] = array();
            }
            $this->patches[$target][] = $patch;
        }
    }

    private function doProcessTemplates($templateDir, $overwrite = false): void
    {
        $targetDir = $this->config->get('dotfiles.home_dir');
        if (!is_dir($templateDir)) {
            $this->debug("Template directory <comment>$templateDir</comment> not found");

            return;
        }

        $finder = Finder::create()
            ->in($templateDir)
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->files()
        ;
        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $source = $file->getRealPath();
            $relativePathName = $this->normalizePathName($file->getRelativePathname());

            $target = $targetDir.DIRECTORY_SEPARATOR.$relativePathName;
            $this->copy($source, $target);
        }
    }

    private function normalizePathName(string $relativePathName)
    {
        if (0 !== strpos($relativePathName, '.')) {
            $relativePathName = '.'.$relativePathName;
        }

        return $relativePathName;
    }

    private function processSection(OutputInterface $output, $section): void
    {
        $config = $this->config;
        $baseDir = $config->get('dotfiles.base_dir');
        $output->writeln("Processing <comment>$section</comment> section");
        $this->doProcessTemplates($baseDir.'/'.$section.'/templates');
        $this->doProcessPatch($baseDir.'/'.$section.'/patch');
        $this->doProcessBin($baseDir.'/'.$section.'/bin');
        $this->doProcessInstallHook($baseDir.'/'.$section.'/hooks');
    }
}
