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

use Dotfiles\Core\DI\Parameters;
use PHPUnit\Framework\TestCase;

/**
 * Class ParameterTest.
 *
 * @covers \Dotfiles\Core\DI\Parameters
 */
class ParameterTest extends TestCase
{
    public function testArrayAccess(): void
    {
        $parameters = new Parameters();
        $parameters['foo'] = 'bar';
        $this->assertTrue(isset($parameters['foo']));
        $this->assertEquals('bar', $parameters['foo']);
        $this->assertEquals('bar', $parameters->get('foo'));
        unset($parameters['foo']);
        $this->assertFalse(isset($parameters['foo']));
    }
}
