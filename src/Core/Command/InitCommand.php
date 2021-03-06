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
use Dotfiles\Core\Exceptions\InvalidOperationException;
use Dotfiles\Core\Util\CommandProcessor;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class InitCommand extends Command
{
    /**
     * @var CommandProcessor
     */
    private $commandProcessor;

    /**
     * @var Config
     */
    private $config;

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

    public function __construct(?string $name = null, CommandProcessor $processor, Config $config)
    {
        parent::__construct($name);
        $this->commandProcessor = $processor;
        $this->config = $config;
        $this->defaultHomeDir = $config->get('dotfiles.home_dir');
        $this->defaultBackupDir = $config->get('dotfiles.backup_dir');
    }

    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription('Initialize new Dotfiles project.')
            ->addArgument('backup-dir', InputArgument::OPTIONAL, 'Local repository directory')
            ->addOption('machine', 'm', InputOption::VALUE_OPTIONAL, 'Machine name')
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

        $this->initDotfilesDir($backupDir, $machine);
        $this->initBackupDir($backupDir);
    }

    private function doAskMachineName()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $default = gethostname();
        $question = new Question(sprintf('Please enter your machine name (<comment>%s</comment>):', $default), $default);

        return $helper->ask($input, $output, $question);
    }

    private function doBackupRepoDir()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $default = 'dev' === getenv('DOTFILES_ENV') ? sys_get_temp_dir().'/dotfiles/backup' : getcwd();
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

    private function initBackupDir($backupDir): void
    {
        Toolkit::ensureDir($backupDir);
        $origin = __DIR__.'/../Resources/templates/backup';

        $finder = Finder::create()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->in($origin)
            ->files()
        ;
        $fs = new Filesystem();
        $fs->mirror($origin, $backupDir, $finder);
    }

    private function initDotFilesDir(string $backupDir, string $machine): void
    {
        $dotfilesDir = $this->defaultHomeDir.DIRECTORY_SEPARATOR.'.dotfiles';
        Toolkit::ensureDir($dotfilesDir);
        $envFile = $dotfilesDir.DIRECTORY_SEPARATOR.'.env';
        $contents = <<<EOF

DOTFILES_MACHINE_NAME=$machine
DOTFILES_BACKUP_DIR=$backupDir

EOF;

        file_put_contents($envFile, $contents, LOCK_EX);
    }
}
