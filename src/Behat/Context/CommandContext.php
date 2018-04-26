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

namespace Dotfiles\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class CommandContext implements Context
{
    private $commandPrefix;

    private $output;

    private $stream;

    public function __construct(string $commandPrefix)
    {
        $this->commandPrefix = $commandPrefix;
    }

    /**
     * @BeforeSuite
     *
     * @throws \Exception when not in docker environment
     */
    public static function beforeSuite(): void
    {
        if (!is_file('/.dockerenv')) {
            throw new \Exception('You must run behat test in docker-environment');
        }

        static::loadDotEnv();
        static::loadPharAutoload();
    }

    /**
     * @Given I execute add command with :exec argument
     * @Given I execute add command with :exec
     *
     * @param string $path
     */
    public function iExecuteAddCommand(string $path): void
    {
        $this->resetStream();
        $this->runCommand('add '.$path);
    }

    /**
     * @Given I execute :command
     *
     * @param string $command
     */
    public function iExecuteCommand(string $command): void
    {
        $this->resetStream();
        $this->runCommand($command);
    }

    /**
     * @Given I execute restore command
     */
    public function iExecuteRestoreCommand(): void
    {
        $this->resetStream();
        $this->runCommand('restore');
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee($text): void
    {
        rewind($this->output->getStream());
        $display = stream_get_contents($this->output->getStream());
        //$display = str_replace(PHP_EOL, "\n", $display);
        Assert::contains($display, $text);
    }

    private static function loadDotEnv(): void
    {
        putenv('DOTFILES_TEMP_DIR='.sys_get_temp_dir().'/dotfiles');
        $files = array(
            __DIR__.'/../Resources/default.env',
        );

        if (is_file($file = getenv('HOME').'/.dotfiles_profile')) {
            $files[] = $file;
        }

        $env = new Dotenv();
        foreach ($files as $file) {
            $env->load($file);
        }
    }

    private static function loadPharAutoload(): void
    {
        if (false !== ($pharFile = getenv('DOTFILES_PHAR_FILE'))) {
            $path = realpath(__DIR__.'/../build/dotfiles.phar');
            $phar = 'phar://'.$path;

            $autoloadFile = $phar.'/vendor/autoload.php';
            $contents = file_get_contents($autoloadFile);

            $pattern = '/ComposerAutoloaderInit[a-z|0-9]+/im';
            preg_match($pattern, $contents, $matches);
            $class = $matches[0];

            if (!class_exists($class)) {
                include_once $autoloadFile;
            }
        }
    }

    private function resetStream(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = fopen('php://memory', 'w');
        $this->output = new StreamOutput($this->stream);
        $this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
    }

    private function runCommand($command): void
    {
        $cmd = $this->commandPrefix.' -vvv '.$command;
        $output = $this->output;
        $helper = new DebugFormatterHelper();

        $process = new Process($cmd, getcwd());
        $process->run(function ($type, $buffer) use ($output,$helper,$process): void {
            $contents = $helper->start(
                spl_object_hash($process),
                $buffer,
                Process::ERR === $type
            );
            $output->writeln($contents);
        });
    }
}
