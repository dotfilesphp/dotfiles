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

use Dotfiles\Plugins\PHPBrew\PHPBrewPlugin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PHPBrewPluginTest extends TestCase
{
    public function testLoad(): void
    {
        $builder = $this->createMock(ContainerBuilder::class);
        $builder->expects($this->once())
            ->method('fileExists')
            ->with($this->stringContains('services.yaml'))
        ;

        $plugin = new PHPBrewPlugin();
        $plugin->load(array(), $builder);
    }
}
