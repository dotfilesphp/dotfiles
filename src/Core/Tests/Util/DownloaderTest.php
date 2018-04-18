<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests\Util;

use Dotfiles\Core\Tests\Helper\BaseTestCase;
use Dotfiles\Core\Util\Downloader;

/**
 * Class DownloaderTest.
 *
 * @covers \Dotfiles\Core\Util\Downloader
 */
class DownloaderTest extends BaseTestCase
{
    public function testHandleNotification()
    {
        $dl = $this->getDownloader();

        $dl->handleNotification(
            STREAM_NOTIFY_FILE_SIZE_IS,
            null,
            null,
            null,
            null,
            5000
        );

        $dl->handleNotification(
            STREAM_NOTIFY_PROGRESS,
            null,
            null,
            null,
            2500,
            5000
        );

        $dl->handleNotification(
            STREAM_NOTIFY_COMPLETED,
            null,
            null,
            null,
            null,
            5000
        );

        $dl->handleNotification(
            STREAM_NOTIFY_REDIRECTED,
            null,
            null,
            null,
            null,
            null
        );
        $display = $this->getDisplay();
        $this->assertContains('50%', $display);
        $this->assertContains('100%', $display);
        $this->assertContains('Download redirected!', $display);
    }

    private function getDownloader()
    {
        return new Downloader(
            $this->getService('dotfiles.output'),
            $this->getService('dotfiles.logger'),
            $this->getParameters()
        );
    }
}
