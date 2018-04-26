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
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Dotfiles\Core\Exceptions\InvalidArgumentException;
use Dotfiles\Core\Util\Toolkit;
use Webmozart\Assert\Assert;

class FileContext implements Context
{
    /**
     * @var CommandContext
     */
    private $mainContext;

    /**
     * @Given Dotfile :name should contain :contents
     *
     * @param string $name
     * @param string $contents
     */
    public function dotfileShouldContain(string $name, string $needle): void
    {
        $target = getenv('DOTFILES_HOME_DIR').DIRECTORY_SEPARATOR.$name;
        if (!is_file($target)) {
            throw new InvalidArgumentException('Can not find file: '.$name);
        }
        Toolkit::ensureFileDir($target);
        $contents = file_get_contents($target);
        Assert::contains($contents, $needle);
    }

    /**
     * @Given Dotfile :name should not contain :contents
     *
     * @param string $name
     * @param string $contents
     */
    public function dotfileShouldNotContain(string $name, string $needle): void
    {
        $target = getenv('DOTFILES_HOME_DIR').DIRECTORY_SEPARATOR.$name;
        if (!is_file($target)) {
            throw new InvalidArgumentException('Can not find file: '.$name);
        }
        Toolkit::ensureFileDir($target);
        $contents = file_get_contents($target);
        Assert::notContains($contents, $needle);
    }

    /**
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        $this->mainContext = $environment->get(CommandContext::class);
    }

    /**
     * @Given I have backup :section patch :path with:
     *
     * @param string       $path
     * @param PyStringNode $contents
     */
    public function iHaveBackupDefaultsPatch(string $section, string $path, PyStringNode $contents = null): void
    {
        if ('machine' === $section) {
            $section = getenv('DOTFILES_MACHINE_NAME');
        }
        $target = getenv('DOTFILES_BACKUP_DIR').'/src/'.$section.'/patch/'.$path;
        $this->generateFile($target, $contents);
    }

    /**
     * @Given I have dotfile :name
     * @Given I have dotfile :name with:
     *
     * @param string $path     Where to create file directory
     * @param string $contents A file contents
     */
    public function iHaveDotfile(string $path, PyStringNode $contents = null): void
    {
        $target = getenv('DOTFILES_HOME_DIR').DIRECTORY_SEPARATOR.$path;
        Toolkit::ensureFileDir($target);
        if (null === $contents) {
            touch($target);
        } else {
            $contents = $contents->getStrings();
            file_put_contents($target, $contents, LOCK_EX);
        }
    }

    private function generateFile($target, PyStringNode $contents = null): void
    {
        Toolkit::ensureFileDir($target);
        if (null === $contents) {
            touch($target);
        } else {
            $contents = $contents->getStrings();
            file_put_contents($target, $contents, LOCK_EX);
        }
    }
}
