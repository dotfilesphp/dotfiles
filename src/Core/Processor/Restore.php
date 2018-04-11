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

namespace Dotfiles\Core\Processor;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class Restore
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $hooks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $patches = array();

    /**
     * @var string
     */
    private $section;

    public function __construct(
        Config $config,
        Dispatcher $dispatcher,
        OutputInterface $output,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->output = $output;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function run(): void
    {
        $output = $this->output;
        $machine = getenv('DOTFILES_MACHINE_NAME');
        $config = $this->config;
        $sections = array('defaults', $machine);

        $this->registerHooks();
        $this->doPreRestoreHooks();
        foreach ($sections as $name) {
            $this->section = $name;
            $output->writeln("Processing <comment>$name</comment> section.");
            $backupDir = $config->get('dotfiles.backup_dir')."/src/{$name}";

            $this->processHomeDir($backupDir.'/home');
            $this->registerPatch($backupDir.'/patch');

            $output->writeln('');
            $output->writeln('');
        }

        $this->section = 'patch';
        $this->debug('applying patch');
        $this->processPatch();

        $this->doPostRestoreHooks();
    }

    private function debug($message, $options = array()): void
    {
        $message = sprintf('[%s] %s', $this->section, $message);
        $this->output->writeln($message, $options);
    }

    private function doPostRestoreHooks(): void
    {
        $hooks = $this->hooks['post']['restore'];
        $this->output->writeln('Processing post-restore hooks');
        foreach ($hooks as $relPath => $realPath) {
            $this->doProcessHooks($relPath, $realPath);
        }
    }

    private function doPreRestoreHooks(): void
    {
        $hooks = $this->hooks['pre']['restore'];
        $this->output->writeln('Processing pre-restore hooks');
        foreach ($hooks as $relPath => $realPath) {
            $this->doProcessHooks($relPath, $realPath);
        }
    }

    private function doProcessHooks($relPath, $realPath): void
    {
        $helper = new DebugFormatterHelper();
        $output = $this->output;
        $output->writeln("Executing <comment>$relPath</comment>");
        $process = new Process($realPath);
        $process->run(function ($type, $buffer) use ($relPath,$output,$helper,$process): void {
            $contents = $helper->start(
                spl_object_hash($process),
                $buffer,
                Process::ERR === $type
            );

            $output->writeln(sprintf($contents));
        });
    }

    private function processHomeDir($homeDir): void
    {
        $targetDir = $this->config->get('dotfiles.home_dir');
        if (!is_dir($homeDir)) {
            $this->debug("Home directory <comment>$homeDir</comment> not found");

            return;
        }

        $files = Finder::create()
            ->in($homeDir)
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->files()
        ;

        $fs = new Filesystem();
        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($files as $file) {
            $target = Toolkit::ensureDotPath($file->getRelativePathname());
            $fs->copy($file->getRealPath(), $targetDir.DIRECTORY_SEPARATOR.$target);
            $this->debug('+restore: <comment>'.$target.'</comment>');
        }
    }

    /**
     * Processing all registered patch.
     */
    private function processPatch(): void
    {
        $event = new PatchEvent($this->patches);
        $dispatcher = $this->dispatcher;
        $dispatcher->dispatch(PatchEvent::NAME, $event);

        $patches = $event->getPatches();
        $homeDir = $this->config->get('dotfiles.home_dir');
        $fs = new Filesystem();
        foreach ($patches as $relPath => $patch) {
            $contents = implode(PHP_EOL, $patch);
            $target = $homeDir.DIRECTORY_SEPARATOR.$relPath;
            $fs->patch($target, $contents);
        }
    }

    private function registerHooks(): void
    {
        $this->hooks['pre']['restore'] = array();
        $this->hooks['post']['restore'] = array();

        $this->section = 'init';
        $backupPath = $this->config->get('dotfiles.backup_dir');
        $finder = Finder::create()
            ->in($backupPath.'/src')
            ->path('hooks')
            ->name('pre-*')
            ->name('post-*')
        ;

        /* @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relPath = $file->getRelativePathname();
            $realPath = $file->getRealPath();

            $baseName = basename($file->getRealPath());
            if (false !== ($tlength = strpos($baseName, '.'))) {
                $baseName = substr($baseName, 0, $tlength);
            }
            $exp = explode('-', $baseName);

            if (!is_executable($realPath)) {
                $this->debug('-hooks not executable: '.$relPath);

                continue;
            }
            $type = $exp[0];
            $hookOn = $exp[1];
            $this->hooks[$type][$hookOn][$relPath] = $realPath;
            $this->debug('+hooks '.$relPath);
        }
    }

    private function registerPatch($patchDir): void
    {
        if (!is_dir($patchDir)) {
            $this->debug('no patch directory found, skipping');

            return;
        }

        $finder = Finder::create()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->in($patchDir)
        ;

        /* @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relPath = Toolkit::ensureDotPath($file->getRelativePathname());
            $patch = file_get_contents($file->getRealPath());
            if (!isset($this->patches[$relPath])) {
                $this->patches[$relPath] = array();
            }
            $this->patches[$relPath][] = $patch;
            $this->debug('+patch '.$relPath);
        }
    }
}
