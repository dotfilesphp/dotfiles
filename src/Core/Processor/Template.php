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
use Dotfiles\Core\Event\RestoreEvent;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

class Template implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var string
     */
    private $section;

    public function __construct(
        Parameters $parameters,
        Dispatcher $dispatcher,
        LoggerInterface $logger
    ) {
        $this->parameters = $parameters;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Constant::EVENT_PRE_RESTORE => array('onPreRestore', 255),
            Constant::EVENT_RESTORE => array('onRestore', 255),
        );
    }

    public function onPreRestore(RestoreEvent $event)
    {
        $files = $this->registerFiles();
        $event->setFiles($files);
    }

    public function onRestore(RestoreEvent $event): void
    {
        $this->restore($event->getFiles());
    }

    private function findBackupFiles($dir)
    {
        $finder = Finder::create()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->in($dir)
        ;
        $files = array();
        foreach ($finder->files() as $file) {
            $dotFile = Toolkit::ensureDotPath($file->getRelativePathName());
            $files[$dotFile] = $file->getRealpath();
        }

        return $files;
    }

    private function registerFiles()
    {
        $params = $this->parameters;
        $sources = $params->get('dotfiles.backup_dir').'/src';
        $machineName = $params->get('dotfiles.machine_name');

        $defaults = $machine = array();
        if (is_dir($dir = $sources.'/defaults/home')) {
            $defaults = $this->findBackupFiles($dir);
        }
        if (is_dir($dir = $sources.'/'.$machineName.'/home')) {
            $machine = $this->findBackupFiles($dir);
        }
        $files = array_merge($defaults, $machine);

        return $files;
    }

    private function restore($files)
    {
        $homeDir = $this->parameters->get('dotfiles.home_dir');
        $fs = new Filesystem();
        foreach ($files as $relativePath => $source) {
            $target = $homeDir.DIRECTORY_SEPARATOR.$relativePath;
            $fs->copy($source, $target);
        }
    }
}
