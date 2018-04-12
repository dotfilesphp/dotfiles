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

namespace Dotfiles\Core\Tests\Processor;

use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Processor\Hooks;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Tests\Helper\LoggerOutputTrait;

class HooksTest extends BaseTestCase
{
    use LoggerOutputTrait;

    private $dispatcher;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->setUpLogger();

        $this->dispatcher = $this->createMock(Dispatcher::class);
    }

    public function getTestHook()
    {
        return array(
            array('-hooks not executable: defaults/hooks/pre-restore.bash'),
            array('defaults pre-restore bash hooks'),
            array('defaults pre-restore php hooks'),
            array('machine pre-restore bash hooks'),
            array('machine pre-restore php hooks'),
            array('defaults post-restore bash'),
            array('machine post-restore bash'),
        );
    }

    /**
     * @param string $expected
     * @dataProvider getTestHook
     */
    public function testHook(string $expected): void
    {
        $hooks = $this->getHookObject();
        $hooks->run();
        $output = $this->getDisplay();

        $this->assertContains($expected, $output);
    }

    /**
     * @return Hooks
     */
    private function getHookObject()
    {
        $config = $this->getConfig();
        $config->set('dotfiles.backup_dir', __DIR__.'/fixtures/backup');

        return new Hooks($config, $this->dispatcher, $this->logger);
    }
}
