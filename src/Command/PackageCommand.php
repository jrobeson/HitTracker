<?php declare(strict_types=1);
/**
 * Copyright (C) 2017 Johnny Robeson <jrobeson@lazerball.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace LazerBall\HitTracker\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

define('DS', DIRECTORY_SEPARATOR);

class PackageCommand extends Command
{
    /** @var OutputInterface */
    private $out;

    protected function configure(): void
    {
        $this
            ->setName('package')
            ->addArgument('target-dir', InputArgument::OPTIONAL, 'Target directory (defaults to temporary directory.')
            ->addOption('build-version', null, InputOption::VALUE_REQUIRED, 'Version to append to the target directory.')
            ->addOption('build-platform', null, InputOption::VALUE_REQUIRED, 'Platform.')
            ->addOption('build-type', null, InputOption::VALUE_REQUIRED, 'Build Type.')
            ->addOption('use-existing-vendor', null, InputOption::VALUE_NONE, 'Use existing vendor dir?')
            ->addOption('compress', null, InputOption::VALUE_NONE, 'Compress the directory?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $targetDir = $input->getArgument('target-dir');
        $buildType = $input->getOption('build-type');
        $platform = $input->getOption('build-platform');
        $version = $input->getOption('build-version');
        $doCompress = $input->getOption('compress');
        $useExistingVendor = $input->getOption('use-existing-vendor');

        if (!$targetDir) {
            $dir = 'hittracker';
            if ($buildType) {
                $dir .= "-$buildType";
            }
            if ($platform) {
                $dir .= "-$platform";
            }
            if ($version) {
                $dir .= "-$version";
            }

            $targetDir = implode(DS, [sys_get_temp_dir(), $dir]);
        }

        if (!$this->getFs()->exists($targetDir)) {
            $output->writeln("Creating $targetDir");
            $this->getFs()->mkdir($targetDir);
        }
        $sourceDir = realpath(dirname(dirname(__DIR__)));

        $this->out = $output;
        $output->writeln('Copying files');
        $this->copyFiles($sourceDir, $targetDir, $useExistingVendor);
        $output->writeln('Running composer install');
        $this->composerInstall($targetDir);
        $output->writeln('Cleaning files');
        $vendorDir = implode(DS, [$targetDir, 'vendor']);
        $this->cleanVendor($vendorDir);

        $output->writeln('Move licenses');
        $licenseDir = implode(DS, [$targetDir, 'third-party-licenses']);
        $this->moveLicenses($targetDir, $licenseDir);

        if ($doCompress) {
            $fileBaseName = implode(DS, [$sourceDir, basename($targetDir).'.tar']);
            $output->writeln(sprintf('Creating Compressed File: %s', $fileBaseName));
            $this->compressTargetDir($targetDir, $fileBaseName);
            $this->getFs()->remove($targetDir);
        }
        $output->writeln('Finished');
    }

    private function compressTargetDir(string $sourceDir, string $targetFile): void
    {
        $fs = $this->getFs();
        $fileName = $targetFile.'.bz2';
        // PharData will try to reuse an existing file
        foreach ([$fileName, $targetFile] as $oldPath) {
            if ($fs->exists($oldPath)) {
                $fs->remove($oldPath);
            }
        }
        $archive = new \PharData($targetFile);
        $archive->buildFromDirectory($sourceDir);

        $this->out->writeln('Compressing Archive');

        $archive->compress(\Phar::BZ2);
    }

    private function copyFiles(string $sourceDir, string $targetDir, bool $useExistingVendor = false): void
    {
        $appDirs = ['bin', 'etc', 'migrations', 'src', 'public', 'translations', 'templates'];
        if ($useExistingVendor) {
            $appDirs[] = 'vendor';
        }

        foreach ($appDirs as $appDir) {
            $this->out->writeln(sprintf('Copying %s', $appDir));
            $this->getFs()->mirror(implode(DS, [$sourceDir, $appDir]), implode(DS, [$targetDir, $appDir]));
        }
        foreach (['composer.json', 'LICENSE'] as $appFile) {
            $this->out->writeln(sprintf('Copying %s', $appFile));
            $this->getFs()->copy(implode(DS, [$sourceDir, $appFile]), implode(DS, [$targetDir, $appFile]));
        }
    }

    private function cleanVendor(string $vendorDir): void
    {
        $fs = $this->getFs();
        // Finder excludes dot files and vcs directories by default
        $vendorDirs = Finder::create()->in($vendorDir)
                ->directories()
                ->ignoreVCS(false)
                ->ignoreDotFiles(false)
                ->name('.git')
                ->name('.hg')
                ->name('benchmarks')
                ->name('doc-templates') // ocramius
                ->name('doc')
                ->name('docs')
                ->name('examples')
                ->name('features') // behat
                ->name('spec') // phpspec
                ->name('Tests')
                ->name('tests')
        ;
        $fs->remove($vendorDirs);

        $vendorFiles = Finder::create()->in($vendorDir)
            ->files()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
            ->name('.gitattributes')
            ->name('.gitignore')
            ->name('.gitmodules')
            ->name('build.properties')
            ->name('build.properties.dev')
            ->name('build.xml')
            ->name('humbug.json.dist')
            ->name('phpunit.*')
            ->name('appveyor.yml')  // not everybody uses .appveyor.yml files yet
            ->name('/CONTRIBUTING/i')
            ->name('/CHANGELOG/i$')
            ->name('/CHANGELOG\.(md|txt)$/i')
            ->name('/CHANGES$/i')
            ->name('/README$/i')
            ->name('/README\.(md|markdown|rst|txt)$/i')
        ;
        $fs->remove($vendorFiles);
    }

    private function moveLicenses(string $sourceDir, string $targetDir): void
    {
        $fs = $this->getFs();
        $fs->mkdir($targetDir);
        $vendorDir = implode(DS, [$sourceDir, 'vendor']);
        $vendorLicenseFiles = Finder::create()->in($vendorDir)
            ->files()
            ->name('COPYING*')
            ->name('LICENSE*')
        ;
        foreach ($vendorLicenseFiles as $vendorLicenseFile) {
            $path = $vendorLicenseFile->getRealPath();
            list($vendorName, $vendorPackageName) = explode(DS, str_replace($vendorDir.DS, '', $path));
            $licenseFileName = $vendorLicenseFile->getBasename();
            $licensePath = $targetDir.DS.$vendorName.'-'.$vendorPackageName.'-'.$licenseFileName;

            $fs->rename($vendorLicenseFile->getRealPath(), $licensePath, true);
        }
    }

    private function composerInstall(string $targetDir): void
    {
        try {
            $composerInstallCmd = "composer install --working-dir=$targetDir --no-dev --prefer-dist --no-scripts"
                                  .' --optimize-autoloader --classmap-authoritative --no-suggest --no-interaction';

            $this->out->writeln($composerInstallCmd);
            $composerInstall = new Process($composerInstallCmd, null, null, null, 300);
            $composerInstall->mustRun();
            $this->out->writeln($composerInstall->getOutput());
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
            exit(1);
        }
    }

    private function getFs(): FileSystem
    {
        return new Filesystem();
    }
}
