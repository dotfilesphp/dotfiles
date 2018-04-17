<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests\DI\Compiler;

use Dotfiles\Core\Console\Application;
use Dotfiles\Core\DI\Compiler\DefaultPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DefaultPassTest extends TestCase
{
    public function testProcess()
    {
        $builder = new ContainerBuilder();
        $builder->addCompilerPass(new DefaultPass());

        $locator = new FileLocator(__DIR__.'/fixtures');
        $loader = new YamlFileLoader($builder, $locator);
        $loader->load('services.yaml');

        $builder->compile();

        $this->assertTrue($builder->has('dotfiles.app'));
        $this->assertTrue($builder->has(Application::class));
    }
}
