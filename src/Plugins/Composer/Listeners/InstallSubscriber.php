<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\Composer\Listeners;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

class InstallSubscriber implements EventSubscriberInterface
{
    const SCRIPT_URL = 'https://getcomposer.org/installer';
    const SIG_URL = 'https://composer.github.io/installer.sig';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Downloader
     */
    private $downloader;

    public function __construct(
        OutputInterface $output,
        LoggerInterface $logger,
        Config $config,
        Downloader $downloader
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->output = $output;
        $this->downloader = $downloader;
    }

    public static function getSubscribedEvents()
    {
        return array(
            InstallEvent::NAME => 'onInstallEvent',
        );
    }

    public function onInstallEvent()
    {
        $config = $this->config;
        $targetDir = $config->get('dotfiles.bin_dir');
        $targetFile = $targetDir.DIRECTORY_SEPARATOR.$config->get('composer.file_name');
        $this->debug('begin installation');
        $this->debug('target file: '.$targetFile);

        $this->debug('checking existing composer installation');
        if (is_file($targetFile)) {
            $this->output->writeln('Composer already installed');

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

    private function executeInstallScript($scriptFile, $installDir, $fileName)
    {
        $cmd = array(
            'php',
            $scriptFile,
            '--ansi',
            '--install-dir='.$installDir,
            '--filename='.$fileName,
        );
        $cmd = implode(' ', $cmd);

        $process = new Process($cmd);
        $process->setTimeout(3600);
        $process->setIdleTimeout(60);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->logger->error($buffer);
            } else {
                $this->debug($buffer);
            }
        });
    }

    private function debug($message, $context = array())
    {
        $message = '<comment>composer:</comment> '.$message;
        $this->logger->debug($message, $context);
    }
}
