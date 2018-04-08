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

namespace Dotfiles\Core\Tests\Config;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Config\DefinitionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\NodeInterface;

class ConfigTest extends TestCase
{
    /**
     * @var string
     */
    private $cachePath;

    protected function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $cachePath = sys_get_temp_dir().'/dotfiles/test/cache/config.php';
        if (is_file($cachePath)) {
            unlink($cachePath);
        }
        $this->cachePath = $cachePath;
    }

    public function testAddConfigAndCacheDir(): void
    {
        $config = new Config();
        $dir = __DIR__.'/fixtures/config';
        $config->addConfigDir($dir);
        $this->assertContains($dir, $config->getConfigDirs());

        $this->assertContains(getcwd(), $config->getCachePath());
        $this->expectException(\InvalidArgumentException::class);
        $config->addConfigDir('foo/bar');
    }

    public function testAddDefinition(): void
    {
        $node = $this->createMock(NodeInterface::class);
        $tree = $this->createMock(TreeBuilder::class);
        $tree->expects($this->once())
            ->method('buildTree')
            ->willReturn($node)
        ;
        $definition = $this->createMock(DefinitionInterface::class)
        ;
        $definition->expects($this->once())
            ->method('getConfigTreeBuilder')
            ->willReturn($tree)
        ;

        $config = new Config();
        $config->addDefinition($definition);
    }

    public function testArrayAccess(): void
    {
        $config = new Config();
        $config['foo'] = 'bar';
        $this->assertTrue(isset($config['foo']));
        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals('bar', $config->get('foo'));
        unset($config['foo']);
        $this->assertFalse(isset($config['foo']));
    }

    public function testLoadConfigurationProcessConfigFiles(): void
    {
        $config = new Config();
        $config->setCachePath($this->cachePath);

        $config->addConfigDir(__DIR__.'/fixtures/config');
        $config->addDefinition(new TestDefinition());
        $config->loadConfiguration();

        $this->assertEquals('bar', $config->get('test.foo'));
        $this->assertEquals('world', $config->get('test.hello'));
    }

    public function testLoadConfigurationProcessDefaultValue(): void
    {
        $config = new Config();
        $config->setCachePath($this->cachePath);

        $config->addDefinition(new TestDefinition());
        $config->loadConfiguration();

        $this->assertEquals('default', $config->get('test.foo'));
        $this->assertEquals('default', $config->get('test.hello'));

        $flattened = $config->getAll(true);
        $this->assertArrayHasKey('test.foo', $flattened);
        $array = $config->getAll();
        $this->assertArrayHasKey('test', $array);
        $this->assertEquals('default', $array['test']['foo']);

        $this->expectException(\InvalidArgumentException::class);
        $config->get('hello.world');
    }

    public function testShouldHandleError(): void
    {
        $config = new Config();
        $config
            ->addDefinition(new TestDefinition())
            ->setCachePath($this->cachePath)
            ->addConfigDir(__DIR__.'/fixtures/error')
        ;
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Unrecognized option "error" under "test"');
        $config->loadConfiguration();
    }
}
