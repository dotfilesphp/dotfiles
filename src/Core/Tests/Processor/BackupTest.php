<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests\Processor;

use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\RestoreEvent;
use Dotfiles\Core\Processor\Backup;
use Dotfiles\Core\Tests\Helper\BaseTestCase;

/**
 * Class BackupTest.
 *
 * @covers \Dotfiles\Core\Processor\Backup
 */
class BackupTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $events = Backup::getSubscribedEvents();
        $this->assertArrayHasKey(Constant::EVENT_PRE_RESTORE, $events);
    }

    public function testInfo()
    {
        $this->createBackupDirMock(__DIR__.'/fixtures/backup-manifest');
        $backup = new Backup(
            $this->getParameters(),
            $this->getService('dotfiles.logger'),
            $this->getService('dotfiles.output')
        );

        $backup->info();
        $display = $this->getDisplay();

        $this->assertContains('zeus', $display);
        $this->assertContains('athena', $display);

        $manifest = $backup->getManifest();
        $version1 = $manifest[0];
        $this->assertEquals('zeus', $version1['machine']);
        $this->assertEquals('2016-04-18 23:58:59', $version1['created']);
        $this->assertEquals('2016-04-18-23-58-59.zip', $version1['file']);
    }

    public function testOnPreRestore()
    {
        $backup = $this->getBackup();

        $event = new RestoreEvent();
        $event->setFiles(array(
            '.ssh/id_rsa' => 'some/file',
            '.ssh/id_rsa.pub' => 'some/file',
            '.foobar' => 'foo/bar',
        ));
        $event->setPatches(array(
            '.bashrc' => 'some/file',
        ));

        $backup->onPreRestore($event);
        $display = $this->getDisplay();
        $this->assertContains('created backup in ', $display);

        // checking zip file contents
        $manifest = $backup->getManifest();
        $fileName = $backup->getBackupDir().DIRECTORY_SEPARATOR.$manifest[0]['file'];
        $files = array();
        $zip = zip_open($fileName);
        while ($zip_entry = zip_read($zip)) {
            $files[] = zip_entry_name($zip_entry);
        }
        zip_close($zip);
        $this->assertTrue(in_array('.bashrc', $files));
        $this->assertTrue(in_array('.ssh/id_rsa', $files));
        $this->assertTrue(in_array('.ssh/id_rsa.pub', $files));
        $this->assertFalse(in_array('.foobar', $files));
    }

    private function getBackup()
    {
        static::cleanupTempDir();
        $this->createHomeDirMock(__DIR__.'/fixtures/home');

        return new Backup(
            $this->getParameters(),
            $this->getService('dotfiles.logger'),
            $this->getService('dotfiles.output')
        );
    }
}
