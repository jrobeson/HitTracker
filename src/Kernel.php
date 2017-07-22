<?php declare(strict_types=1);
/*
 * Copyright (C) 2017 Johnny Robeson <johnny@localmomentum.net>
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

namespace LazerBall\HitTracker;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use UnexpectedValueException;
use function Cekurte\Environment\env;

final class Kernel extends BaseKernel
{
    /** @var string */
    private $buildType;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $environment, bool $debug, string $buildType)
    {
        $this->buildType = $buildType;

        $isProd = 'production' === $environment;
        if (!$isProd && $debug) { /* @todo check this */
            \Symfony\Component\Debug\Debug::enable();
        }

        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        $contents = require dirname(__DIR__).'/etc/bundles.php';
        foreach ($contents as $class => $envs) {
            if (empty($envs) || (isset($envs['all']) || isset($envs[$this->environment]))) {
                yield new $class();
            }
        }
    }

    protected function getBuildType(): string
    {
        return $this->buildType;
    }

    /** @return string[] */
    private function getConfigFiles(string $environment, string $buildType): array
    {
        $configFiles = [
            'parameters_default.yaml',
            sprintf('%s/parameters.yaml', $buildType),
            'config.yaml',
            sprintf('%s/config.yaml', $buildType),
            sprintf('config_%s.yaml', $environment),
            sprintf('%s/config_%s.yaml', $environment, $buildType),
        ];
        if ('test' === $environment) { // test requires dev files first
            array_splice($configFiles, 2, 0, [
                'config_development.yaml',
                sprintf('%s/config_development.yaml', $buildType),
            ]);
        }

        $configFiles = array_map(function ($fileName) {
            return sprintf('%s/etc/%s', $this->getProjectDir(), $fileName);
        }, $configFiles);

        return $configFiles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configFiles = $this->getConfigFiles($this->getEnvironment(), $this->getBuildType());

        foreach ($configFiles as $configFile) {
            if (file_exists($configFile)) {
                $loader->load($configFile);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * Force root directory to be %kernel.project_dir/app, so bundle overrides can be found
     * can still be found.
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $this->rootDir = implode(DIRECTORY_SEPARATOR, [realpath(dirname(__DIR__)), 'app']);
        }

        return $this->rootDir;
    }

    public function getCacheDir(): string
    {
        $cacheDir = env('HITTRACKER_CACHE_DIR');
        if ($cacheDir) {
            return $cacheDir;
        }

        $varDir = env('HITTRACKER_VAR_DIR');
        if ($varDir) {
            return implode(DIRECTORY_SEPARATOR, [$varDir, 'cache']);
        }

        switch ($this->buildType) {
            case 'electron':
                if (!$varDir) {
                    throw new UnexpectedValueException('"HITTRACKER_VAR_DIR" env var must be set for Electron.');
                }
                break;
            case 'appliance':
            case 'hosted':
                if (!$cacheDir) {
                    $cacheDir = implode(DIRECTORY_SEPARATOR, ['', 'var', 'lib', 'hittracker', $this->environment]);
                }
                break;
            default:
                $cacheDir = implode(DIRECTORY_SEPARATOR, [
                    $this->getProjectDir(),
                    'var', 'cache',
                    $this->getBuildType(),
                    $this->environment,
                ]);
        }

        return $cacheDir;
    }

    public function getLogDir(): string
    {
        $logDir = env('HITTRACKER_LOG_DIR');
        if ($logDir) {
            return $logDir;
        }

        $varDir = env('HITTRACKER_VAR_DIR');
        if ($varDir) {
            return implode(DIRECTORY_SEPARATOR, [$varDir, 'log']);
        }

        switch ($this->buildType) {
            case 'electron':
                if (!$varDir) {
                    throw new UnexpectedValueException('"HITTRACKER_VAR_DIR" env var must be set for Electron.');
                }
                break;
            case 'appliance':
            case 'hosted':
                    $logDir = implode(DIRECTORY_SEPARATOR, ['', 'var', 'log', 'hittracker']);
                break;
            default:
                $logDir = implode(DIRECTORY_SEPARATOR, [
                    $this->getProjectDir(),
                    'var', 'logs',
                    $this->getBuildType(),
                ]);
        }

        return $logDir;
    }

    protected function getKernelParameters(): array
    {
        $kernelParameters = parent::getKernelParameters();
        $kernelParameters['kernel.config_dir'] = implode(DIRECTORY_SEPARATOR, [realpath($this->getProjectDir()), 'etc']);
        $kernelParameters['kernel.public_dir'] = implode(DIRECTORY_SEPARATOR, [realpath($this->getProjectDir()), 'public']);

        return $kernelParameters;
    }
}
