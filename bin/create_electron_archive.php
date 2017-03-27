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
}, ['app', 'bin', 'etc', 'migrations', 'src', 'web']
);
$appFiles->in($appDirs)->files();

$vendorFiles = new Finder();
$vendorFiles->in($archiveDir.'/vendor')
    ->exclude([
        'benchmarks',
        'doc-templates', // ocramius
        'doc',
        'docs',
        'examples',
        'features', // behat
        'spec', // phpspec
        'Tests',
        'tests'
    ])
    ->files()
    ->notName('build.properties')
    ->notName('build.properties.dev')
    ->notName('build.xml')
    ->notName('humbug.json.dist')
    ->notName('phpunit.*')
    ->notName('appveyor.yml')  // not everybody uses .appveyor.yml files yet
    ->notName('/CONTRIBUTING/i')
    ->notName('/CHANGELOG/i$')
    ->notName('/CHANGELOG\.(md|txt)$/i')
    ->notName('/CHANGES$/i')
    ->notName('/README$/i')
    ->notName('/README\.(md|markdown|rst|txt)$/i')
;

$appFiles->append($vendorFiles);

$archive = new PharData($fileBaseName);

echo "Building Archive...\n";
$archive->buildFromIterator($appFiles, $archiveDir);

echo "Compressing Archive...\n";
$archive->compress(Phar::BZ2);

// Phar gets too greedy with the the '.' tokens when creating a .tar.bz2 filename, so we "fix" it.
$fs->rename(str_replace("-$version.tar.bz2", '-0.tar.bz2', $fileName), $fileName);
echo "Finished\n";
