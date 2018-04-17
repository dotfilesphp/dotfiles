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

namespace Dotfiles\Core\Tests\Event;

use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Tests\Helper\BaseTestCase;

/**
 * Class PatchEventTest.
 *
 * @covers \Dotfiles\Core\Event\PatchEvent
 */
class PatchEventTest extends BaseTestCase
{
    public function testAddPatch(): void
    {
        $patches = array(
            'current' => array(
                'current-content',
            ),
            'replace' => array(
                'not-visible',
            ),
        );
        $event = new PatchEvent($patches);
        $event->addPatch('current', 'current-added-content');
        $event->addPatch('new', 'new-content');
        $event->addPatch('new', 'new-added-content');
        $event->setPatch('replace', array('replaced-content'));

        $patches = $event->getPatches();
        $contents = var_export($patches, true);

        $this->assertContains('current-content', $contents);
        $this->assertContains('current-added-content', $contents);
        $this->assertContains('new-content', $contents);
        $this->assertContains('new-added-content', $contents);
        $this->assertContains('replaced-content', $contents);
    }
}
