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

namespace Dotfiles\Core\Tests\DI;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\DI\Builder;
use Dotfiles\Core\Tests\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * Class BuilderTest.
 *
 * @covers \Dotfiles\Core\DI\Builder
 */
class BuilderTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $config;

    public function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
    }

    public function getBuilder()
    {
        $tempDir = sys_get_temp_dir().'/dotfiles/tests/builder';
        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.cache_dir', $tempDir.'/var/cache'),
            ))
        ;

        return new Builder($this->config);
    }

    public function testCacheFileName(): void
    {
        // check default cache file generation
        $builder = $this->getBuilder();
        $this->assertContains('var/cache', $builder->getCacheFileName());
    }

    public function testCompile(): void
    {
        $definition = $this->createMock(Definition::class);

        $cb = $this->createMock(ContainerBuilder::class);
        $definition->expects($this->any())
            ->method('setPublic')
            ->will($this->returnSelf())
        ;
        $contents = <<<EOC
<?php

use Symfony\Component\DependencyInjection\Container;

class CachedContainer extends Container
{
}
EOC;
        $dumper = $this->createMock(PhpDumper::class);
        $dumper->expects($this->once())
            ->method('dump')
            ->with(array('class' => 'CachedContainer'))
            ->willReturn($contents)
        ;
        $builder = $this->getBuilder();
        $cacheFileName = $builder->getCacheFileName();
        $builder->setContainerBuilder($cb)
            ->setDumper($dumper)
        ;
        $builder->compile();
        $this->assertFileExists($cacheFileName);
        $this->assertEquals($contents, file_get_contents($cacheFileName));

        $this->assertInstanceOf(Container::class, $builder->getContainer());
    }

    public function testDefaults(): void
    {
        $builder = $this->getBuilder();
        $this->assertInstanceOf(ContainerBuilder::class, $builder->getContainerBuilder());

        $cb = $this->createMock(ContainerBuilder::class);
        $cb->expects($this->once())
            ->method('isCompiled')
            ->willReturn(true)
        ;
        $builder->setContainerBuilder($cb);
        $this->assertInstanceOf(DumperInterface::class, $builder->getDumper());
    }
}
