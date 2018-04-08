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

use Dotfiles\Core\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

abstract class BaseTestCase extends TestCase
{
    public static function setUpBeforeClass(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUpBeforeClass();
        $finder = Finder::create()
            ->in(sys_get_temp_dir().'/dotfiles')
        ;
        $fs = new Filesystem();
        $fs->remove($finder->files());
    }
}
