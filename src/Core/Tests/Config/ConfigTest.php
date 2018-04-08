<?php

namespace Dotfiles\Core\Tests\Config;

use PHPUnit\Framework\TestCase;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\NodeInterface;


class ConfigTest extends TestCase
{
    /**
     * @var string
     */
    private $cachePath;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $cachePath = sys_get_temp_dir().'/dotfiles/test/cache/config.php';
        if(is_file($cachePath)){
            unlink($cachePath);
        }
        $this->cachePath = $cachePath;
    }

    public function testArrayAccess()
    {
        $config = new Config();
        $config['foo'] = 'bar';
        $this->assertTrue(isset($config['foo']));
        $this->assertEquals('bar',$config['foo']);
        $this->assertEquals('bar',$config->get('foo'));
        unset($config['foo']);
        $this->assertFalse(isset($config['foo']));
    }

    public function testAddConfigAndCacheDir()
    {
        $config = new Config();
        $dir = __DIR__.'/fixtures/config';
        $config->addConfigDir($dir);
        $this->assertContains($dir,$config->getConfigDirs());

        $this->assertContains(getcwd(),$config->getCachePath());
        $this->expectException(\InvalidArgumentException::class);
        $config->addConfigDir('foo/bar');
    }

    public function testAddDefinition()
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

    public function testLoadConfigurationProcessDefaultValue()
    {
        $config = new Config();
        $config->setCachePath($this->cachePath);

        $config->addDefinition(new TestDefinition());
        $config->loadConfiguration();

        $this->assertEquals('default',$config->get('test.foo'));
        $this->assertEquals('default',$config->get('test.hello'));

        $flattened = $config->getAll(true);
        $this->assertArrayHasKey('test.foo',$flattened);
        $array = $config->getAll();
        $this->assertArrayHasKey('test',$array);
        $this->assertEquals('default',$array['test']['foo']);

        $this->expectException(\InvalidArgumentException::class);
        $config->get('hello.world');
    }

    public function testLoadConfigurationProcessConfigFiles()
    {
        $config = new Config();
        $config->setCachePath($this->cachePath);

        $config->addConfigDir(__DIR__.'/fixtures/config');
        $config->addDefinition(new TestDefinition());
        $config->loadConfiguration();

        $this->assertEquals('bar',$config->get('test.foo'));
        $this->assertEquals('world',$config->get('test.hello'));
    }

    public function testShouldHandleError()
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
