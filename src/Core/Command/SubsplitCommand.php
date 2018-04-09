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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SubsplitCommand extends Command
{
    public const SOURCE = 'git@github.com:kilip/dotfiles.git';
    /**
     * @var OutputInterface
     */
    private $output;
    private $workdir;

    public function handleProcessRun($type, $buffer): void
    {
        $contents = '<info>output:</info> '.$buffer;
        if (Process::ERR == $type) {
            $contents = '<error>error:</error> '.$buffer;
        }
        $this->output->write($contents);
    }

    protected function configure(): void
    {
        $this->setName('subsplit');
        $this->workdir = realpath(__DIR__.'/../../../');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;
        $workdir = $this->workdir;

        if (!is_dir($dir = $workdir.'/.subsplit')) {
            $this->runCommand('git subsplit --debug init '.static::SOURCE);
        } else {
            $this->runCommand('git subsplit --debug update '.static::SOURCE);
        }

        $tree = array(
            'core' => array(
                'path' => 'src/Core',
                'repo' => 'git@github.com:dotfilesphp/core.git',
            ),
            'bash' => array(
                'path' => 'src/Plugins/Bash',
                'repo' => 'git@github.com:dotfilesphp/bash-plugin.git',
            ),
            'phpbrew' => array(
                'path' => 'src/Plugins/PHPBrew',
                'repo' => 'git@github.com:dotfilesphp/phpbrew-plugin.git',
            ),
            'bashit' => array(
                'path' => 'src/Plugins/BashIt',
                'repo' => 'git@github.com:dotfilesphp/bashit-plugin.git',
            ),
            'composer' => array(
                'path' => 'src/Plugins/Composer',
                'repo' => 'git@github.com:dotfilesphp/composer-plugin.git',
            ),
            'phpcsfixer' => array(
                'path' => 'src/Plugins/PHPCSFixer',
                'repo' => 'git@github.com:dotfilesphp/phpcsfixer-plugin.git',
            ),
        );

        foreach ($tree as $name => $config) {
            $this->output->writeln("processing <comment>$name</comment>");
            $this->publish($config['path'], $config['repo']);
        }
    }

    private function publish($path, $repo, $heads = 'master', $tag = null): void
    {
        $command = array(
            'git',
            'subsplit',
            'publish',
            '--heads='.$heads,
        );
        if (null !== $tag) {
            $command[] = '--tags='.$tag;
        }

        $command[] = $path.':'.$repo;

        $command = implode(' ', $command);
        //$this->output->writeln("<comment>$command</comment>");
        $this->runCommand($command);
    }

    private function runCommand($command): void
    {
        $process = new Process($command, $this->workdir);
        $process->run(array($this, 'handleProcessRun'));
    }
}
