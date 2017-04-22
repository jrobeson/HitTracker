#!/usr/bin/env php
<?php declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';

if (!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) {
    echo 'Usage: create_archive <build_type> <platform> <version>';
    exit(1);
}

$buildType = $argv[1];
$platform = $argv[2];
$version = $argv[3];

$archiveDir = realpath(__DIR__.'/../');
$fileBaseName = "$archiveDir/hittracker-$buildType-$platform-$version.tar";
$fileName = $fileBaseName.'.bz2';
$tmpDir = "/tmp/hittracker-$buildType-$version";

ini_set('memory_limit', '-1');

$fs = new Filesystem();

// PharData will try to reuse an existing file
foreach ([$fileName, $fileBaseName, $tmpDir] as $oldPath) {
    if ($fs->exists($oldPath)) {
        $fs->remove($oldPath);
    }
}

$fs->mkdir($tmpDir);

$appDirs = ['app', 'bin', 'etc', 'migrations', 'src', 'web'];
if ('hosted' === $buildType) {
    $appDirs[] = 'systemcfg';
}

echo "Copying Files...\n";
foreach ($appDirs as $appDir) {
    echo sprintf("Copying %s\n", $appDir);
    $fs->mirror($archiveDir.'/'.$appDir, $tmpDir.'/'.$appDir);
}

foreach (['composer.json', 'composer.lock', 'LICENSE'] as $appFile) {
    echo sprintf("Copying %s\n", $appFile);
    $fs->copy("$archiveDir/$appFile", $tmpDir.'/'.$appFile);
}

echo "Installing (composer)...\n";

try {
    $composerInstallCmd = "composer install --working-dir=$tmpDir --no-dev --prefer-dist --no-scripts --optimize-autoloader --classmap-authoritative --no-suggest";
    $composerInstall = new Process($composerInstallCmd);
    $composerInstall->mustRun();
    echo $composerInstall->getOutput();
} catch (ProcessFailedException $e) {
    echo $e->getMessage();
    exit(1);
}

echo "Removing Unused files and directories...\n";
$vendorDir = $tmpDir.'/vendor';
// Finder excludes dot files and vcs directories by default
$vendorDirs = Finder::create()->in($vendorDir)
        ->directories()
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

echo "Moving licenses...\n";
$licenseDir = "$tmpDir/third-party-licenses";
$fs->mkdir($licenseDir);
$vendorLicenseFiles = Finder::create()->in($vendorDir)
    ->files()
    ->name('COPYING*')
    ->name('LICENSE*')
;
foreach ($vendorLicenseFiles as $vendorLicenseFile) {
    $path = $vendorLicenseFile->getRealPath();
    list($vendorName, $vendorPackageName) = explode('/', str_replace($vendorDir.'/', '', $path));
    $licenseFileName = $vendorLicenseFile->getBaseName();
    $licensePath = "$licenseDir/$vendorName-$vendorPackageName-$licenseFileName";

    $fs->rename($vendorLicenseFile->getRealPath(), $licensePath, true);
}


echo "Creating File: $fileName\n";
$archive = new PharData($fileBaseName);

echo "Building Archive...\n";
$archive->buildFromDirectory($tmpDir);

echo "Compressing Archive...\n";
$archive->compress(Phar::BZ2);

$fs->remove($tmpDir);
// Phar gets too greedy with the the '.' tokens when creating a .tar.bz2 filename, so we "fix" it.
$fs->rename(str_replace("-$version.tar.bz2", '-0.tar.bz2', $fileName), $fileName);
echo "Finished\n";
