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
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class AddCommand extends Command implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        ?string $name = null,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($name);
        $this->config = $config;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setName('add')
            ->setDescription('Add new file into dotfiles manager')
            ->addArgument('path', InputArgument::REQUIRED, 'A file or directory name to add. This file must be exists in $HOME directory')
            ->addOption('machine', '-m', InputOption::VALUE_OPTIONAL, 'Add this file/directory into machine registry', 'default')
            ->addOption('recursive', '-r', InputOption::VALUE_NONE, 'Import all directory contents recursively')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws InvalidOperationException when path not exists
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $config = $this->config;
        $homeDir = $config->get('dotfiles.home_dir');
        $recursive = $input->getOption('recursive');
        $machine = $input->getOption('machine');
        $repoDir = $config->get('dotfiles.repo_dir')."/src/$machine/home";
        $sourcePath = str_replace($homeDir.DIRECTORY_SEPARATOR, '', $input->getArgument('path'));

        // detect source path
        $originPath = $this->detectPath($sourcePath);
        $targetPath = $repoDir.'/'.str_replace('.', '', $sourcePath);

        Toolkit::ensureDir($repoDir);

        $basename = basename($sourcePath);
        if (0 === strpos($basename, '.')) {
            $targetPath = str_replace('.'.$sourcePath, $basename, $targetPath);
        }

        if (is_dir($originPath)) {
            $this->doAddDir($originPath, $targetPath, $recursive);
        } else {
            $this->doAddFile($originPath, $targetPath);
        }
    }

    private function detectPath($path)
    {
        if ($this->ensureDirOrFile($path)) {
            return $path;
        }

        $homeDir = $this->config->get('dotfiles.home_dir');
        $test = $homeDir.DIRECTORY_SEPARATOR.$path;
        if ($this->ensureDirOrFile($test)) {
            return $test;
        }

        if ($this->ensureDirOrFile($test = $homeDir.DIRECTORY_SEPARATOR.'.'.$path)) {
            return $test;
        }

        throw new InvalidOperationException("Can not find directory or file to process. Please make sure that $path is exists");
    }

    /**
     * @param string $origin
     * @param string $target
     * @param bool   $recursive
     *
     * @throws InvalidOperationException
     */
    private function doAddDir(string $origin, string $target, $recursive): void
    {
        if (!$recursive) {
            throw new InvalidOperationException('Add dir without recursive');
        }

        $finder = Finder::create()
            ->in($origin)
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
        ;
        $fs = new Filesystem();
        $fs->mirror($origin, $target, $finder);
        $this->output->writeln(sprintf(
            'copy files from <comment>%s</comment> to <comment>%s</comment>',
            $origin,
            $target
        ));
    }

    /**
     * @param string $origin
     * @param string $target
     */
    private function doAddFile(string $origin, string $target): void
    {
        $fs = new Filesystem();
        $fs->copy($origin, $target);
        $this->output->writeln(
            sprintf(
                'copy from <comment>%s</comment> to <comment>%s</comment>',
                $origin,
                $target
            )
        );
    }

    private function ensureDirOrFile($path)
    {
        return is_file($path) || is_dir($path);
    }
}
