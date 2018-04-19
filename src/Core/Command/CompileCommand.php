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

namespace Dotfiles\Core\Command;

use Seld\PharUtils\Timestamps;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Class CompileCommand.
 *
 * @codeCoverageIgnore
 */
class CompileCommand extends Command
{
    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var string
     */
    private $branchAliasVersion = '';

    /**
     * @var array
     */
    private $files = array();

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $version;

    /**
     * @var \DateTime
     */
    private $versionDate;

    public function compile($pharFile = 'dotfiles.phar'): void
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $this->setupVersion();
        $this->generatePhar($pharFile);
    }

    /**
     * @return string
     */
    public function getBranchAliasVersion(): string
    {
        return $this->branchAliasVersion;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return \DateTime
     */
    public function getVersionDate(): \DateTime
    {
        return $this->versionDate;
    }

    protected function configure(): void
    {
        $this
            ->setName('compile')
            ->setDescription('generate new dotfiles.phar')
            ->addArgument('target', InputArgument::OPTIONAL, 'Compile dotfiles.phar into this directory', getcwd().'/build')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $cwd = getcwd();
        chdir(dirname(__DIR__.'/../../../../'));

        $this->baseDir = getcwd();
        $this->output = $output;

        // start compiling process
        $targetDir = realpath($input->getArgument('target'));
        $target = $targetDir.'/dotfiles.phar';
        $this->compile($target);
        $this->generateVersionFile($targetDir);
        chmod($target, 0755);

        chdir($cwd);

        $output->writeln("Completed! dotfiles.phar generated in <comment>$target</comment>");
    }

    private function addDotfilesBin($phar): void
    {
        $content = file_get_contents($this->baseDir.'/bin/dotfiles');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/dotfiles', $content);
    }

    /**
     * @param $phar
     * @param \SplFileInfo $file
     * @param bool         $strip
     */
    private function addFile($phar, \SplFileInfo $file, $strip = true): void
    {
        $path = $this->getRelativeFilePath($file);
        $content = file_get_contents($file->getRealPath());
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n".$content."\n";
        }

        if ('src/Core/Application.php' === $path) {
            $content = str_replace('@package_version@', $this->version, $content);
            $content = str_replace('@package_branch_alias_version@', $this->branchAliasVersion, $content);
            $content = str_replace('@release_date@', $this->versionDate->format('Y-m-d H:i:s'), $content);
        }
        $phar->addFromString($path, $content);
    }

    private function generatePhar($pharFile = 'dotfiles.phar'): void
    {
        $finderSort = function ($a, $b) {
            return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
        };

        $this->output->writeln("Start registering files in <comment>{$this->baseDir}</comment>");
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->exclude(array(
                'Tests',
            ))
            //->notName('Compiler.php')
            //->notName('SubsplitCommand.php')
            //->notName('CompilerCommand.php')
            ->in($this->baseDir.'/src/Core')
            ->sort($finderSort)
        ;
        $this->registerFiles($finder);

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('LICENSE')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->in($this->baseDir.'/vendor/symfony')
            ->in($this->baseDir.'/vendor/composer')
            ->in($this->baseDir.'/vendor/myclabs')
            ->in($this->baseDir.'/vendor/psr')
            ->in($this->baseDir.'/vendor/monolog')
            ->sort($finderSort)
        ;
        $this->registerFiles($finder);

        $finder->files()
            ->ignoreVCS(true)
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->name('*.yaml')
            ->name('*.yml')
            ->name('*.php')
            ->in($this->baseDir.'/src/Plugins')
            ->sort($finderSort)
        ;

        $this->registerFiles($finder);
        $this->files[] = new \SplFileInfo($this->baseDir.'/vendor/autoload.php');

        $phar = new \Phar($pharFile, 0, 'dotfiles.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        $count = count($this->files);
        $this->output->writeln("Start processing <comment>{$count} files</comment>");
        $this->processFiles($phar);
        $this->output->writeln('');
        $this->addDotfilesBin($phar);
        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        unset($phar);

        $util = new Timestamps($pharFile);
        $util->updateTimestamps($this->versionDate);
        $util->save($pharFile, \Phar::SHA1);
    }

    private function generateVersionFile($targetDir): void
    {
        $version = $this->version;
        $branchAlias = $this->branchAliasVersion;
        $date = $this->versionDate->format('Y-m-d H:i:s');
        $sha256 = trim(shell_exec('sha256sum '.$targetDir.'/dotfiles.phar'));
        $sha256 = trim(str_replace($targetDir.'/dotfiles.phar', '', $sha256));

        $contents = <<<EOC
{
    "version": "${version}",
    "branch": "${branchAlias}",
    "date": "${date}",
    "sha256": "${sha256}"
}

EOC;
        file_put_contents($targetDir.'/dotfiles.phar.json', $contents, LOCK_EX);
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return string
     */
    private function getRelativeFilePath($file)
    {
        $realPath = $file->getRealPath();
        $pathPrefix = $this->baseDir.'/';
        $pos = strpos($realPath, $pathPrefix);
        $relativePath = (false !== $pos) ? substr_replace($realPath, '', $pos, strlen($pathPrefix)) : $realPath;

        return strtr($relativePath, '\\', '/');
    }

    private function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php
/*
 * This file is part of dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

// Avoid APC causing random fatal errors per https://github.com/composer/composer/issues/264
if (extension_loaded('apc') && ini_get('apc.enable_cli') && ini_get('apc.cache_by_default')) {
    if (version_compare(phpversion('apc'), '3.0.12', '>=')) {
        ini_set('apc.cache_by_default', 0);
    } else {
        fwrite(STDERR, 'Warning: APC <= 3.0.12 may cause fatal errors when running composer commands.'.PHP_EOL);
        fwrite(STDERR, 'Update APC, or set apc.enable_cli or apc.cache_by_default to 0 in your php.ini.'.PHP_EOL);
    }
}

putenv('DOTFILES_PHAR_MODE=1');

Phar::mapPhar('dotfiles.phar');

EOF;

        // add warning once the phar is older than 60 days
        if (preg_match('{^[a-f0-9]+$}', $this->version)) {
            $warningTime = $this->versionDate->format('U') + 60 * 86400;
            $stub .= "define('COMPOSER_DEV_WARNING_TIME', $warningTime);\n";
        }

        return $stub.<<<'EOF'
require 'phar://dotfiles.phar/bin/dotfiles';

__HALT_COMPILER();
EOF;
    }

    private function processFiles($phar): void
    {
        $files = $this->files;
        $progressBar = new ProgressBar($this->output, count($files));
        $progressBar->setFormat('Compiling <comment>%percent%%</comment>');
        $progressBar->start();
        foreach ($files as $key => $file) {
            $this->addFile($phar, $file);
            $progressBar->advance();
        }

        $progressBar->finish();
    }

    /**
     * @param Finder $finder
     */
    private function registerFiles(Finder $finder): void
    {
        foreach ($finder as $file) {
            if (!in_array($file, $this->files)) {
                $this->files[] = $file;
            }
        }
    }

    private function setupVersion(): void
    {
        $process = new Process('git log --pretty="%H" -n1 HEAD', __DIR__);
        if (0 != $process->run()) {
            throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $this->version = trim($process->getOutput());

        $process = new Process('git log -n1 --pretty=%ci HEAD', __DIR__);
        if (0 != $process->run()) {
            throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from composer git repository clone and that git binary is available.');
        }

        $this->versionDate = new \DateTime(trim($process->getOutput()));
        $this->versionDate->setTimezone(new \DateTimeZone('UTC'));
        $process = new Process('git describe --tags --exact-match HEAD');
        if (0 == $process->run()) {
            $this->version = trim($process->getOutput());
        } else {
            // get branch-alias defined in composer.json for dev-master (if any)
            $localConfig = getcwd().'/composer.json';
            $contents = file_get_contents($localConfig);
            $json = json_decode($contents, true);
            if (isset($json['extra']['branch-alias']['dev-master'])) {
                $this->branchAliasVersion = $json['extra']['branch-alias']['dev-master'];
            }
        }
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }
}
