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

namespace Dotfiles\Plugins\PHPBrew\Tests;

use Dotfiles\Plugins\PHPBrew\Configuration;
use Dotfiles\Plugins\PHPBrew\PHPBrewPlugin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class PHPBrewPluginTest
 *
 * @package Dotfiles\Plugins\PHPBrew\Tests
 */
class PHPBrewPluginTest extends TestCase
{
    public function testLoad(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $builder->expects($this->once())
            ->method('fileExists')
            ->with($this->stringContains('services.yaml'))
        ;

        $builder->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(Configuration::class))
        ;

        $parameter = $this->createMock(ParameterBagInterface::class);
        $builder->expects($this->once())
            ->method('getParameterBag')
            ->willReturn($parameter)
        ;

        $parameter->expects($this->once())
            ->method('add')
            ->with([
                'phpbrew.rc_enable' => true,
                'phpbrew.set_prompt' => true,
            ])
        ;

        $plugin = new PHPBrewPlugin();
        $plugin->load(array(), $builder);
    }
}
