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
use Dotfiles\Core\Event\RestoreEvent;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Patcher implements EventSubscriberInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var array
     */
    private $patches = array();

    public function __construct(
        Parameters $parameters,
        LoggerInterface $logger,
        Dispatcher $dispatcher
    ) {
        $this->parameters = $parameters;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Constant::EVENT_PRE_RESTORE => array('onPreRestore'),
            Constant::EVENT_POST_RESTORE => array('onPostRestore'),
            Constant::EVENT_PATCH => array('onPatchEvent'),
        );
    }

    public function onPatchEvent()
    {
        $this->registerPatch();
        $this->run();
    }

    public function onPostRestore(): void
    {
        $this->run();
    }

    public function onPreRestore(RestoreEvent $event)
    {
        $this->registerPatch();
        $event->setPatches($this->patches);
    }

    /**
     * Processing all registered patch.
     *
     * @param array $patches
     */
    private function applyPatch($patches): void
    {
        $homeDir = $this->parameters->get('dotfiles.home_dir');
        $this->debug('start applying patch to '.$homeDir);
        $fs = new Filesystem();
        foreach ($patches as $relPath => $patch) {
            $contents = implode(PHP_EOL, $patch);
            $target = $homeDir.DIRECTORY_SEPARATOR.$relPath;
            $this->debug('+patch '.$target);
            Toolkit::ensureFileDir($target);
            $fs->patch($target, $contents);
        }
    }

    private function debug($message, array $context = array()): void
    {
        $this->logger->info($message, $context);
    }

    private function registerPatch(): void
    {
        $machine = $this->parameters->get('dotfiles.machine_name');
        $backupDir = $this->parameters->get('dotfiles.backup_dir').'/src';
        $dirs = array();
        if (is_dir($dir = $backupDir.'/defaults/patch')) {
            $dirs[] = $dir;
        }
        $machinePatch = $backupDir.DIRECTORY_SEPARATOR.$machine.DIRECTORY_SEPARATOR.'/patch';
        if (is_dir($machinePatch)) {
            $dirs[] = $machinePatch;
        }

        if (!count($dirs) > 0) {
            return;
        }
        $finder = Finder::create()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->in($dirs)
        ;

        $this->debug('registering all available patches');

        /* @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relPath = $file->getRelativePathname();
            $relPath = Toolkit::ensureDotPath($relPath);
            $patch = file_get_contents($file->getRealPath());
            if (!isset($this->patches[$relPath])) {
                $this->patches[$relPath] = array();
            }
            $this->patches[$relPath][] = $patch;
            $this->debug('+patch '.$relPath);
        }
    }

    private function run()
    {
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
}
