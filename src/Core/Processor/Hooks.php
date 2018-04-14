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

use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Event\Dispatcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class Hooks
{
    /**
     * @var \Dotfiles\Core\DI\Parameters
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

    public function __construct(
        Parameters $config,
        Dispatcher $dispatcher,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    public function run(): void
    {
        $this->hooks = array();
        $this->registerHooks();
        $this->doPreRestoreHooks();
        $this->doPostRestoreHooks();
    }

    private function debug($messages, $context = array()): void
    {
        $this->logger->info($messages, $context);
    }

    private function doPostRestoreHooks(): void
    {
        $hooks = $this->hooks['post']['restore'];
        $this->debug('Processing post-restore hooks');
        foreach ($hooks as $relPath => $realPath) {
            $this->doProcessHooks($relPath, $realPath);
        }
    }

    private function doPreRestoreHooks(): void
    {
        $hooks = $this->hooks['pre']['restore'];
        $this->debug('Start Processing pre-restore hooks');
        foreach ($hooks as $relPath => $realPath) {
            $this->doProcessHooks($relPath, $realPath);
        }
    }

    private function doProcessHooks($relPath, $realPath): void
    {
        $helper = new DebugFormatterHelper();
        $logger = $this->logger;
        $logger->debug("Executing <comment>$relPath</comment>");
        $process = new Process($realPath);
        $process->run(function ($type, $buffer) use ($relPath,$logger,$helper,$process): void {
            $buffer = trim($buffer);
            $contents = $helper->start(
                spl_object_hash($process),
                $buffer,
                Process::ERR === $type
            );
            $logger->debug('<comment>[hooks] </comment>'.$contents);
        });
    }

    private function registerHooks(): void
    {
        $this->hooks['pre']['restore'] = array();
        $this->hooks['post']['restore'] = array();

        $backupPath = $this->config->get('dotfiles.backup_dir').'/src';
        $machine = $this->config->get('dotfiles.machine_name');
        $dirs = array();
        if (is_dir($dir = $backupPath.'/defaults/hooks')) {
            $dirs[] = $dir;
        }
        if (is_dir($dir = $backupPath.'/'.$machine.'/hooks')) {
            $dirs[] = $dir;
        }

        if (!count($dirs) > 0) {
            $this->debug('+hooks no available hooks found');

            return;
        }

        $finder = Finder::create()
            ->in($backupPath)
            ->name('pre-*')
            ->name('post-*')
            ->path('defaults/hooks')
            ->path($machine.'/hooks')
        ;

        /* @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relPath = $file->getRelativePathname();
            $realPath = $file->getRealPath();

            $this->debug($file->getRealPath());
            $baseName = basename($file->getRealPath());
            if (false !== ($tlength = strpos($baseName, '.'))) {
                $baseName = substr($baseName, 0, $tlength);
            }
            $exp = explode('-', $baseName);

            $this->debug($file->getRealPath());
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
}
