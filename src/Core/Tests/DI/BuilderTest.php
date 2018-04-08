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

use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\DI\Builder;
use Dotfiles\Core\Event\Dispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BuilderTest extends TestCase
{
    public function testCacheFileName(): void
    {
        // check default cache file generation
        $builder = new Builder();
        $this->assertContains(getcwd(), $builder->getCacheFileName());

        // check if directory should be created when not exists
        $dir = sys_get_temp_dir().'/dotfiles/test/cache/some-dir';
        if (is_dir($dir)) {
            rmdir($dir);
        }
        $builder->setCacheFileName($dir.'/some-file.php');
        $this->assertDirectoryExists($dir);
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
        $cacheFileName = sys_get_temp_dir().'/dotfiles/test/container.php';
        $builder = new Builder();
        $builder->setCacheFileName($cacheFileName);
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
        $builder = new Builder();
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
