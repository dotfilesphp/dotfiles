<?php

namespace Dotfiles\Core\Tests\Config;

use PHPUnit\Framework\TestCase;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;

class ConfigTest extends TestCase
{
    public function testFactory()
    {
        $config = new Config();
        $this->assertInstanceOf(Config::class,$config);
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

    public function testLoadConfiguration()
    {
        $node = $this->createMock(NodeInterface::class);
        $node->expects($this->once())
            ->method('getName')
            ->willReturn('foo')
        ;
        $node->expects($this->once())
            ->method('finalize')
            ->willReturn(['hello'=>'world'])
        ;
        $tree = $this->createMock(TreeBuilder::class);
        $tree->expects($this->any())
            ->method('buildTree')
            ->willReturn($node)
        ;
        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->any())
            ->method('getConfigTreeBuilder')
            ->willReturn($tree)
        ;
        $config = new Config();
        $cachePath = sys_get_temp_dir().'/dotfiles/test/cache/config.php';
        if(is_file($cachePath)){
            unlink($cachePath);
        }
        $config->setCachePath($cachePath);
        $config->addConfigDir(__DIR__.'/fixtures/config');
        $config->addDefinition($definition);
        $config->loadConfiguration();

        $this->assertTrue(isset($config['foo']));
        $this->assertArrayHasKey('hello',$config['foo']);
        $this->assertEquals('world',$config['foo']['hello']);
        $this->assertEquals('world',$config->get('foo.hello'));
    }
}
