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
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(?string $name = null, CommandProcessor $processor)
    {
        parent::__construct($name);
        $this->commandProcessor = $processor;
    }

    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription('Initialize new Dotfiles project.')
            ->addArgument('repo-dir', InputArgument::OPTIONAL, 'Local repository directory')
            ->addOption('home-dir', 'hd', InputOption::VALUE_OPTIONAL, 'Home directory')
            ->addOption('machine', 'm', InputOption::VALUE_OPTIONAL, 'Machine name')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws InvalidOperationException when git is not installed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (null === ($repoDir = $input->getArgument('repo-dir'))) {
            $repoDir = $this->doAskRepoDir();
        }

        if (null === ($machine = $input->getOption('machine'))) {
            $machine = $this->doAskMachineName();
        }

        if (null === ($homeDir = $input->getOption('home-dir'))) {
            $homeDir = $this->doAskHomeDir();
        }

        $this->initDotfilesDir($homeDir, $repoDir, $machine);
        $this->initRepoDir($repoDir);
    }

    private function doAskHomeDir()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $question = new Question(sprintf('Please enter your home directory (default: <comment>%s</comment>):', getenv('HOME')), getenv('HOME'));

        return $helper->ask($input, $output, $question);
    }

    private function doAskMachineName()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $question = new Question(sprintf('Please enter your machine name (default: <comment>%s</comment>):', gethostname()), gethostname());

        return $helper->ask($input, $output, $question);
    }

    private function doAskRepoDir()
    {
        $input = $this->input;
        $output = $this->output;
        $helper = $this->getHelper('question');
        $question = new Question('Please enter local repository dir: ');
        $question->setValidator(function ($answer) {
            if (null === $answer) {
                throw new InvalidOperationException('You have to define local repository directory');
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

    private function initDotFilesDir(string $homeDir, $repoDir, $machine): void
    {
        $dotfilesDir = $homeDir.DIRECTORY_SEPARATOR.'.dotfiles';
        Toolkit::ensureDir($dotfilesDir);
        $templateDir = __DIR__.'/../Resources/templates/dotfiles';
        $finder = Finder::create()
            ->in($templateDir)
            ->ignoreDotFiles(false)
            ->files()
        ;
        $fs = new Filesystem();
        $fs->mirror($templateDir, $dotfilesDir,$finder);

        $envFile = $dotfilesDir.DIRECTORY_SEPARATOR.'.env';
        $contents = file_get_contents($envFile);
        $contents = strtr($contents, array(
            '{{machine_name}}' => $machine,
            '{{repo_dir}}' => $repoDir,
        ));
        file_put_contents($envFile, $contents, LOCK_EX);
    }

    private function initRepoDir($repoDir): void
    {
        Toolkit::ensureDir($repoDir);
        $fs = new Filesystem();
        $fs->mirror(__DIR__.'/../Resources/templates/repo', $repoDir);
    }
}
