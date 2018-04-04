<?php

namespace Dotfiles\Core\Tests\DI;

use Dotfiles\Core\DI\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Dotfiles\Core\DI\Builder;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class BuilderTest extends TestCase
{
    public function testDefaults()
    {
        $builder = new Builder();
        $this->assertInstanceOf(ContainerBuilder::class,$builder->getContainerBuilder());

        $cb = $this->createMock(ContainerBuilder::class);
        $cb->expects($this->once())
            ->method('isCompiled')
            ->willReturn(true)
        ;
        $builder->setContainerBuilder($cb);
        $this->assertInstanceOf(DumperInterface::class,$builder->getDumper());
    }

    public function testCacheFileName()
    {
        // check default cache file generation
        $builder = new Builder();
        $this->assertContains(getcwd(),$builder->getCacheFileName());

        // check if directory should be created when not exists
        $dir = sys_get_temp_dir().'/dotfiles/test/cache/some-dir';
        if(is_dir($dir)){
            rmdir($dir);
        }
        $builder->setCacheFileName($dir.'/some-file.php');
        $this->assertDirectoryExists($dir);
    }

    public function testCompile()
    {
        $definition = $this->createMock(Definition::class);

        $cb = $this->createMock(ContainerBuilder::class);
        $definition->expects($this->any())
            ->method('setPublic')
            ->will($this->returnSelf())
        ;
        $cb->expects($this->exactly(3))
            ->method('register')
            ->willReturn($definition)
            ->withConsecutive(
                [Dispatcher::class],
                [Config::class],
                [Application::class]
            )
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
            ->with(['class' => 'CachedContainer'])
            ->willReturn($contents)
        ;
        $cacheFileName = sys_get_temp_dir().'/dotfiles/test/container.php';
        $builder = new Builder();
        $builder->setCacheFileName($cacheFileName);
        $builder->setContainerBuilder($cb)
            ->setDumper($dumper)
        ;
        $builder->compile();
        $this->assertFileExists($cacheFileName);
        $this->assertEquals($contents,file_get_contents($cacheFileName));

        $this->assertInstanceOf(Container::class,$builder->getContainer());
    }
}
