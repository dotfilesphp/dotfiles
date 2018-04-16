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

namespace Dotfiles\Core\Tests\Console;

use Dotfiles\Core\Console\Application;
use Dotfiles\Core\Tests\BaseTestCase;
use Symfony\Component\Console\Input\StringInput;

/**
 * Class ApplicationTest.
 *
 * @covers \Dotfiles\Core\Console\Application
 */
class ApplicationTest extends BaseTestCase
{
    public function testRun()
    {
        $input = new StringInput('list');
        $app = $this->getSUT();
        $app->run($input, $this->output);
        $display = $this->getDisplay();

        $this->assertContains('@package_version@ @package_branch_alias_version@ @release_date@', $display);
    }

    public function testVersion(): void
    {
        $app = $this->getSUT();
        $this->assertEquals('@package_version@', $app->getVersion());
        $expected = implode(' ', array(
            Application::VERSION,
            Application::BRANCH_ALIAS_VERSION,
            Application::RELEASE_DATE,
        ));

        $this->assertEquals($expected, $app->getLongVersion());
    }

    /**
     * @return Application
     */
    private function getSUT()
    {
        return $this->getService('dotfiles.app');
    }
}
