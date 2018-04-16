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

namespace Dotfiles\Plugins\Composer;

use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Processor\ProcessRunner;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Installer
{
    public const SCRIPT_URL = 'https://getcomposer.org/installer';

    public const SIG_URL = 'https://composer.github.io/installer.sig';

    /**
     * @var \Dotfiles\Core\DI\Parameters
     */
    private $config;

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProcessRunner
     */
    private $runner;

    public function __construct(
        OutputInterface $output,
        LoggerInterface $logger,
        Parameters $config,
        Downloader $downloader,
        ProcessRunner $runner
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->output = $output;
        $this->downloader = $downloader;
        $this->runner = $runner;
    }

    /**
     * @param bool $force
     */
    public function run($force = false): void
    {
        $config = $this->config;
        $targetDir = $config->get('dotfiles.bin_dir');
        $targetFile = $targetDir.DIRECTORY_SEPARATOR.$config->get('composer.file_name');
        $this->debug('begin installation');
        $this->debug('target file: '.$targetFile);

        $this->debug('checking existing composer installation');
        if (is_file($targetFile) && !$force) {
            $this->output->writeln('Composer already installed, skipping');

            return;
        }

        $downloader = $this->downloader;
        $scriptFile = $config->get('dotfiles.temp_dir').'/composer/composer.php';
        $sigFile = $config->get('dotfiles.temp_dir').'/composer/composer.sig';
        Toolkit::ensureFileDir($scriptFile);
        Toolkit::ensureFileDir($sigFile);

        if (!is_file($sigFile)) {
            $downloader->run(static::SIG_URL, $sigFile);
        }
        if (!is_file($scriptFile)) {
            $this->debug('start downloading composer');
            $downloader->run(static::SCRIPT_URL, $scriptFile);
        }
        if (!$this->checkSignature($scriptFile, $sigFile)) {
            return;
        }

        // start executing script
        $this->executeInstallScript(
            $scriptFile,
            $config->get('dotfiles.bin_dir'),
            $config->get('composer.file_name')
        );
        if (is_file($targetFile)) {
            $this->output->writeln('composer successfully installed to <comment>'.$targetFile.'</comment>');
        }
    }

    private function checkSignature($scriptFile, $sigFile)
    {
        $actual = hash_file('SHA384', $scriptFile);
        $expected = trim(file_get_contents($sigFile));
        if ($expected !== $actual) {
            $this->output->writeln('<error>Signature Invalid</error>');
            unlink($scriptFile);
            unlink($sigFile);

            return false;
        }
        $this->debug('signature valid');

        return true;
    }

    private function debug($message, $context = array()): void
    {
        $message = '<comment>composer:</comment> '.$message;
        $this->logger->debug($message, $context);
    }

    /**
     * @param $scriptFile
     * @param $installDir
     * @param $fileName
     */
    private function executeInstallScript($scriptFile, $installDir, $fileName): void
    {
        $cmd = array(
            'php',
            $scriptFile,
            '--ansi',
            '--install-dir='.$installDir,
            '--filename='.$fileName,
        );
        $cmd = implode(' ', $cmd);

        $this->runner->run(
            $cmd,
            null,
            null,
            null,
            null,
            null
        );
    }
}
