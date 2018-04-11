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
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class MainContext implements Context
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

    private function resetStream(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = fopen('php://memory', 'w');
        $this->output = new StreamOutput($this->stream);
    }

    private function runCommand($command): void
    {
        $cmd = $this->commandPrefix.' '.$command;
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
