#!/usr/bin/env php
<?php declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

require __DIR__.'/../vendor/autoload.php';

if (!isset($argv[1]) || !isset($argv[2])) {
    echo 'Usage: create_archive <platform> <version>';
    exit(1);
}
$platform = $argv[1];
$version = $argv[2];

$fileBaseName = __DIR__."/../HitTracker-electron-$platform-$version.tar";

$fileName = $fileBaseName.'.bz2';

$archiveDir = realpath(__DIR__.'/../');

ini_set('memory_limit', '-1');
$fs = new Filesystem();

if (file_exists($fileName)) {
    $fs->remove($fileName);
}
if (file_exists($fileBaseName)) {
    $fs->remove($fileBaseName);
}

echo "Creating File: $fileName\n";
echo "Finding Files...\n";
// Finder excludes dot files and vcs directories by default
$appFiles = new Finder();

$appDirs = array_map(function ($d) use ($archiveDir) {
    return $archiveDir.'/'.$d;
}, ['app', 'bin', 'etc', 'src', 'web']
);
$appFiles->in($appDirs)->files();

$vendorFiles = new Finder();
$vendorFiles->in($archiveDir.'/vendor')->files()
    ->notName('doc-template') // ocramius
    ->notName('docs')
    ->notName('examples')
    ->notName('features') // behat
    ->notName('spec')  // phpspec
    ->notName('Tests')
    ->notName('tests')
    ->notName('/^phpunit')
    ->notName('appveyor.yml')  // not everybody uses .appveyor.yml files yet
    ->notName('/CHANGELOG/i')
    ->notName('UPGRADE*')
    ->notName('README*')
;

$appFiles->append($vendorFiles);

$archive = new PharData($fileBaseName);

echo "Building Archive...\n";
$archive->buildFromIterator($appFiles, $archiveDir);

echo "Compressing Archive...\n";
$archive->compress(Phar::BZ2);

// Phar gets too greedy with the the tokens when creating a .tar.bz2 filename, so we "fix" it.
$fs->rename(str_replace("-$version.tar.bz2", '-0.tar.bz2', $fileName), $fileName);
echo "Finished\n";
