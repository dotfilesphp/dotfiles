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

use Dotfiles\Core\Constant;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Patcher
{
    /**
     * @var Parameters
     */
    private $config;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $patches = array();

    public function __construct(
        Parameters $config,
        LoggerInterface $logger,
        Dispatcher $dispatcher
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function run(): void
    {
        $this->registerPatch();
        $dispatcher = $this->dispatcher;
        $patchEvent = new PatchEvent($this->patches);

        // begin to writting patch
        $this->debug('dispatching '.Constant::EVENT_PRE_PATCH);
        $dispatcher->dispatch(Constant::EVENT_PRE_PATCH, $patchEvent);
        $patches = $patchEvent->getPatches();
        $this->applyPatch($patches);
        $this->debug('dispatching '.Constant::EVENT_POST_PATCH);
        $dispatcher->dispatch(Constant::EVENT_POST_PATCH, $patchEvent);
    }

    /**
     * Processing all registered patch.
     *
     * @param array $patches
     */
    private function applyPatch($patches): void
    {
        $this->debug('start applying patch');
        $homeDir = $this->config->get('dotfiles.home_dir');
        $fs = new Filesystem();
        foreach ($patches as $relPath => $patch) {
            $contents = implode(PHP_EOL, $patch);
            $target = $homeDir.DIRECTORY_SEPARATOR.$relPath;
            $fs->patch($target, $contents);
            $this->debug('+patch '.$target);
        }
    }

    private function debug($message, array $context = array()): void
    {
        $this->logger->info($message, $context);
    }

    private function registerPatch(): void
    {
        $backupDir = $this->config->get('dotfiles.backup_dir').'/src';

        $finder = Finder::create()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->in($backupDir)
            ->path('patch')
        ;

        $this->debug('registering all available patches');

        /* @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relPath = str_replace($file->getRelativePath().'/', '', $file->getRelativePathname());
            $relPath = Toolkit::ensureDotPath($relPath);
            $patch = file_get_contents($file->getRealPath());
            if (!isset($this->patches[$relPath])) {
                $this->patches[$relPath] = array();
            }
            $this->patches[$relPath][] = $patch;
            $this->debug('+patch '.$relPath);
        }
    }
}
