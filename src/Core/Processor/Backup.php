<?php

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
use Dotfiles\Core\Event\RestoreEvent;
use Dotfiles\Core\Util\BackupManifest;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Backing up all related files before restore process.
 */
class Backup implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $backupDir;

    /**
     * @var int
     */
    private $lastVersion = 0;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BackupManifest
     */
    private $manifest;

    /**
     * @var string
     */
    private $manifestFile;

    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(
        Parameters $parameters,
        LoggerInterface $logger,
        OutputInterface $output
    ) {
        $this->parameters = $parameters;
        $this->logger = $logger;
        $this->output = $output;
        $this->backupDir = $parameters->get('dotfiles.backup_dir').'/var/backup';
        $this->manifestFile = $this->backupDir.'/manifest.php';
        $this->loadManifest();
    }

    public function getBackupDir()
    {
        return $this->backupDir;
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Constant::EVENT_PRE_RESTORE => array('onPreRestore', -255),
        );
    }

    public function info()
    {
        $output = $this->output;
        if (!is_dir($this->backupDir)) {
            $output->writeln('No backup created yet');

            return;
        }

        $table = new Table($output);
        $table->setHeaders(array('Version', 'Machine', 'Created', 'File'));
        foreach ($this->manifest as $info) {
            $table->addRow(array(
                $info['version'],
                $info['machine'],
                $info['created'],
                $info['file'],
            ));
        }
        $table->render();
    }

    public function onPreRestore(RestoreEvent $event)
    {
        $files = $event->getFiles();
        $patches = $event->getPatches();

        $affectedFiles = array_merge(
            array_keys($files),
            array_keys($patches)
        );
        $affectedFiles = array_unique($affectedFiles);

        $homeDir = $this->parameters->get('dotfiles.home_dir');

        $zip = new \ZipArchive();
        $time = new \DateTime();
        $fileName = $this->backupDir.DIRECTORY_SEPARATOR.$time->format('Y-m-d-H-i-s').'.zip';
        $logger = $this->logger;
        Toolkit::ensureFileDir($fileName);
        $zip->open($fileName, \ZipArchive::CREATE);
        $logger->info('created backup in <comment>'.$fileName.'</comment>');
        foreach ($affectedFiles as $file) {
            $source = $homeDir.DIRECTORY_SEPARATOR.$file;
            if (is_file($source)) {
                $zip->addFile($source, $file);
                $logger->debug('added file to zip: '.$source);
            }
        }
        $zip->close();
        $this->addManifest($fileName);
    }

    private function addManifest($backupFile)
    {
        $time = date_create_from_format('Y-m-d-H-i-s', basename($backupFile, '.zip'));
        $machineName = $this->parameters->get('dotfiles.machine_name');
        $version = $this->lastVersion + 1;
        $this->manifest[] = array(
            'machine' => $machineName,
            'version' => $version,
            'created' => $time->format('Y-m-d H:i:s'),
            'file' => basename($backupFile),
        );

        $exported = var_export($this->manifest, true);
        $time = new \DateTime();
        $template = "<?php\n/* backup manifest updated: %s*/\n\$this->manifest = %s;";
        $contents = sprintf($template, $time->format('Y-m-d H:i:s'), $exported);
        file_put_contents($this->manifestFile, $contents, LOCK_EX);
        $this->loadManifest();
    }

    private function loadManifest()
    {
        if (is_file($file = $this->backupDir.'/manifest.php')) {
            include $file;
            $lastVersion = 0;
            foreach ($this->manifest as $manifest) {
                $version = $manifest['version'];
                if ($version > $lastVersion) {
                    $lastVersion = $version;
                }
            }
            $this->lastVersion = $lastVersion;
        }
    }
}
