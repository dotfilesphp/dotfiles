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

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\ApplicationFactory;
use Dotfiles\Core\Console\Application;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Finder\Finder;

abstract class BaseTestCase extends TestCase
{
    /**
     * @var StreamOutput
     */
    protected $output;

    /**
     * @var resource
     */
    protected $stream;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    private $hasBoot;

    public static function setUpBeforeClass(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUpBeforeClass();
        static::cleanupTempDir();
    }

    public function boot(): void
    {
        if (!$this->hasBoot) {
            $stream = fopen('php://memory', '+w');
            $output = new StreamOutput($stream);

            $factory = new ApplicationFactory();
            $factory->boot();
            $this->container = $factory->getContainer();
            $this->container->set('dotfiles.output', $output);

            $app = new TestApplication(
                $this->container->get('dotfiles.parameters'),
                $this->container->get('dotfiles.input'),
                $output
            );
            $this->container->set(Application::class, $app);
            $this->container->set('dotfiles.app', $app);

            $this->stream = $stream;
            $this->output = $output;
            $this->hasBoot = true;
        }
    }

    public static function cleanupTempDir(): void
    {
        $dir = getenv('DOTFILES_TEMP_DIR');
        if ($dir && is_dir($dir)) {
            $finder = Finder::create()
                ->in($dir)
            ;
            $fs = new Filesystem();
            $fs->remove($finder->files());
            $fs->remove($finder->directories());
        }
    }

    /**
     * @return \Dotfiles\Core\Console\Application
     */
    public function getApplication()
    {
        $this->boot();

        return $this->container->get('dotfiles.app');
    }

    protected function createBackupDirMock(string  $fromFixturesDir): void
    {
        $files = Finder::create()
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->in($fromFixturesDir)
        ;
        $fs = new Filesystem();
        $fs->mirror($fromFixturesDir, $this->getParameters()->get('dotfiles.backup_dir'), $files);
    }

    protected function createHomeDirMock(string $fromFixturesDir): void
    {
        $files = Finder::create()
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->in($fromFixturesDir)
        ;
        $fs = new Filesystem();
        $fs->mirror($fromFixturesDir, $this->getParameters()->get('dotfiles.home_dir'), $files);
    }

    protected function getContainer()
    {
        $this->boot();

        return $this->container;
    }

    protected function getDisplay()
    {
        rewind($this->output->getStream());
        $display = stream_get_contents($this->output->getStream());

        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }

    /**
     * @return \Dotfiles\Core\DI\Parameters
     */
    protected function getParameters(): Parameters
    {
        return $this->getContainer()->get('dotfiles.parameters');
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    protected function getService(string $id)
    {
        return $this->getContainer()->get($id);
    }
}
