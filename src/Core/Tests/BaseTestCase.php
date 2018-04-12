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
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;

abstract class BaseTestCase extends TestCase
{
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
            $factory = new ApplicationFactory();
            $factory->boot();
            $app = new TestApplication();
            $this->container = $factory->getContainer();
            $this->container->set('dotfiles.app', $app);
            $this->hasBoot = true;
        }
    }

    public static function cleanupTempDir(): void
    {
        if (is_dir($dir = sys_get_temp_dir().'/dotfiles')) {
            $finder = Finder::create()
                ->in($dir)
            ;
            $fs = new Filesystem();
            $fs->remove($finder->files());
            $fs->remove($finder->directories());
        }
    }

    /**
     * @return \Dotfiles\Core\Application
     */
    public function getApplication()
    {
        $this->boot();

        return $this->container->get('dotfiles.app');
    }

    /**
     * @return Config
     */
    protected function getConfig(): Config
    {
        return $this->getContainer()->get('dotfiles.config');
    }

    protected function getContainer()
    {
        $this->boot();

        return $this->container;
    }
}
