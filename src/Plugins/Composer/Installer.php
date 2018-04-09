<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Plugins\Composer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dotfiles\Core\Util\CommandProcessor;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Config\Config;
use Symfony\Component\Process\Process;
use Dotfiles\Core\Util\Toolkit;

class Installer
{
    public const SCRIPT_URL = 'https://getcomposer.org/installer';
    public const SIG_URL = 'https://composer.github.io/installer.sig';

    /**
     * @var Config
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
     * @var CommandProcessor
     */
    private $processor;

    public function __construct(
        OutputInterface $output,
        LoggerInterface $logger,
        Config $config,
        Downloader $downloader,
        CommandProcessor $processor
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->output = $output;
        $this->downloader = $downloader;
        $this->processor = $processor;
    }

    public function run()
    {
        $config = $this->config;
        $targetDir = $config->get('dotfiles.bin_dir');
        $targetFile = $targetDir.DIRECTORY_SEPARATOR.$config->get('composer.file_name');
        $this->debug('begin installation');
        $this->debug('target file: '.$targetFile);

        $this->debug('checking existing composer installation');
        if (is_file($targetFile)) {
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

        $process = $this->processor->create($cmd);
        $process->setTimeout(3600);
        $process->setIdleTimeout(60);
        $process->run(function ($type, $buffer): void {
            //@codeCoverageIgnoreStart
            if (Process::ERR === $type) {
                $this->logger->error($buffer);
            } else {
                $this->debug($buffer);
            }
            //@codeCoverageIgnoreEnd
        });
    }
}
