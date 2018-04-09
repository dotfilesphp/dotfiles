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
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class BackupCommand extends Command implements CommandInterface
{
    protected static $defaultName = 'backup';

    /**
     * @var Config
     */
    private $config;

    private $dryRun = false;

    private $files = array();

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Config $config, LoggerInterface $logger)
    {
        parent::__construct();
        $this->config = $config;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->setName('backup');
        $this->setDescription('Backup current dotfiles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->dryRun = $input->hasOption('dry-run') ? $input->getOption('dry-run') : false;
        $backupDir = $this->config->get('dotfiles.backup_dir');
        $manifestFile = $backupDir.DIRECTORY_SEPARATOR.'manifest.php';
        if (is_file($manifestFile)) {
            $output->writeln("Backup files already exists in <comment>$backupDir</comment>");

            return;
        }

        $config = $this->config;
        $baseDir = $config->get('dotfiles.base_dir');
        $machineName = $config->get('dotfiles.machine_name');
        $this->generateFileList($baseDir.'/defaults/templates');
        $this->generateFileList($baseDir.'/defaults/patch');
        $this->generateFileList($baseDir."/machines/{$machineName}/templates");
        $this->generateFileList($baseDir."/machines/{$machineName}/patch");

        $overwrite = $input->hasOption('overwrite-new');
        $fs = new Filesystem();
        foreach ($this->files as $relativePathName => $info) {
            $origin = $info['origin'];
            $target = $info['target'];
            if (!$this->dryRun) {
                $fs->copy($origin, $target, array('overwriteNewerFiles' => $overwrite));
            }

            $this->logger->debug(
                sprintf(
                    'Backup <comment>%s</comment> to <comment>%s</comment>',
                    Toolkit::stripPath($origin),
                    Toolkit::stripPath($target)
                )
            );
        }

        // generate manifest
        $exports = var_export($this->files, true);
        $date = new \DateTime();
        $contents = "<?php\n/* generated at: {$date->format('Y-m-d H:i:s')}*/\nreturn {$exports};\n";
        Toolkit::ensureFileDir($manifestFile);
        file_put_contents(
            $manifestFile,
            $contents,
            LOCK_EX
        );
    }

    private function generateFileList($dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $homeDir = $this->config->get('dotfiles.home_dir');
        $backupDir = $this->config->get('dotfiles.backup_dir');
        $finder = Finder::create()
            ->in($dir)
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
        ;

        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relativePathName = Toolkit::ensureDotPath($file->getRelativePathname());
            $homeFile = $homeDir.DIRECTORY_SEPARATOR.$relativePathName;
            if (!array_key_exists($relativePathName, $this->files) && is_file($homeFile)) {
                $this->files[$relativePathName] = array(
                    'origin' => $homeFile,
                    'target' => $backupDir.DIRECTORY_SEPARATOR.$relativePathName,
                );
            }
        }
    }
}
