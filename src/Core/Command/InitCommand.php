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

use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Exceptions\InvalidOperationException;
use Dotfiles\Core\Util\CommandProcessor;
use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InitCommand extends Command
{
    /**
     * @var CommandProcessor
     */
    private $commandProcessor;

    /**
     * @var string
     */
    private $defaultBackupDir;

    /**
     * @var string
     */
    private $defaultHomeDir;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(?string $name = null, CommandProcessor $processor, Parameters $parameters)
    {
        parent::__construct($name);
        $this->commandProcessor = $processor;
        $this->parameters = $parameters;
        $this->defaultHomeDir = $parameters->get('dotfiles.home_dir');
        $this->defaultBackupDir = $parameters->get('dotfiles.backup_dir');
    }

    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription('Initialize new Dotfiles project.')
            ->addArgument('backup-dir', InputArgument::OPTIONAL, 'Local repository directory')
            ->addOption('machine', 'm', InputOption::VALUE_OPTIONAL, 'Machine name')
            ->addOption('install-dir', 'i', InputOption::VALUE_OPTIONAL, 'Dotfiles instalaltion directory installation')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (null === ($backupDir = $input->getArgument('backup-dir'))) {
            $backupDir = $this->doBackupRepoDir();
        }

        if (null === ($machine = $input->getOption('machine'))) {
            $machine = $this->doAskMachineName();
        }

        if (null === ($installDir = $input->getOption('install-dir'))) {
            $installDir = $this->doAskInstallDir();
        }

        $this->initDotfilesProfile($backupDir, $machine, $installDir);
    }

    private function doAskInstallDir()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $default = getenv('DOTFILES_HOME_DIR').'/.dotfiles';
        $question = new Question(sprintf('Your installation directory (<comment>%s</comment>):', $default), $default);

        return $helper->ask($input, $output, $question);
    }

    private function doAskMachineName()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $default = getenv('DOTFILES_MACHINE_NAME');
        $question = new Question(sprintf('Please enter your machine name (<comment>%s</comment>):', $default), $default);

        return $helper->ask($input, $output, $question);
    }

    private function doBackupRepoDir()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $default = getenv('DOTFILES_BACKUP_DIR');
        $question = new Question("Please enter local backup dir (<comment>$default</comment>): ", $default);
        $question->setValidator(function ($answer) {
            if (null === $answer) {
                throw new InvalidOperationException('You have to define local backup directory');
            }
            $parent = dirname($answer);
            if (!is_dir($parent) || !is_writable($parent)) {
                throw new InvalidOperationException(
                    "Can not find parent directory, please ensure that $parent is exists and writable"
                );
            }

            return $answer;
        });

        $question->setMaxAttempts(3);

        return $helper->ask($input, $output, $question);
    }

    private function initDotfilesProfile(string $backupDir, string $machine, $installDir): void
    {
        $time = (new \DateTime())->format('Y-m-d H:i:s');
        $envFile = getenv('DOTFILES_HOME_DIR').'/.dotfiles_profile';
        Toolkit::ensureFileDir($envFile);
        $contents = <<<EOF
# generated at $time
DOTFILES_MACHINE_NAME=$machine
DOTFILES_BACKUP_DIR=$backupDir
DOTFILES_INSTALL_DIR=$installDir
EOF;

        file_put_contents($envFile, $contents, LOCK_EX);
    }
}
