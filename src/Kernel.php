<?php declare(strict_types=1);
/**
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

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use UnexpectedValueException;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /** @var string */
    private $buildType;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $environment, bool $debug, string $buildType)
    {
        $this->buildType = $buildType;

        if ($debug) {
            \Symfony\Component\Debug\Debug::enable();
        }

        parent::__construct($environment, $debug);
    }

    /** @return string[] */
    public static function getVarsFromEnv(): array
    {
        return [
            'APP_ENV' => $_SERVER['APP_ENV'] ?? 'production',
            'APP_DEBUG' => $_SERVER['APP_DEBUG'] ?? 0,
            'APP_LOG_DIR' => $_SERVER['APP_LOG_DIR'] ?? null,
            'APP_TMP_DIR' => $_SERVER['APP_TMP_DIR'] ?? null,
            'APP_VAR_DIR' => $_SERVER['APP_VAR_DIR'] ?? null,
            'APP_BUILD_TYPE' => $_SERVER['APP_BUILD_TYPE'] ?? 'local',
        ];
    }

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/etc/bundles.php';
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
            sprintf('%s/config_%s.yaml', $buildType, $environment),
        ];
        if ('test' === $environment) { // test requires dev files first
            array_splice($configFiles, 2, 0, [
                sprintf('%s/config_development.yaml', $buildType),
            ]);
        }
        $configFiles = array_map(function ($fileName) {
            return sprintf('%s/etc/%s', $this->getProjectDir(), $fileName);
        }, $configFiles);

        return $configFiles;
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/etc/bundles.php'));

        $container->setParameter('container.dumper.inline_class_loader', true);

        $configFiles = $this->getConfigFiles($this->getEnvironment(), $this->getBuildType());

        $confDir = $this->getProjectDir().'/etc';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');

        foreach ($configFiles as $configFile) {
            if (file_exists($configFile)) {
                $loader->load($configFile);
            }
        }
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().'/etc';

        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }

    public function getCacheDir(): string
    {
        return implode(\DIRECTORY_SEPARATOR, [
                    $this->getProjectDir(),
                    'var', 'cache',
                    $this->environment,
                ]);
    }

    public function getLogDir(): string
    {
        $logDir = self::getVarsFromEnv()['APP_LOG_DIR'];
        if ($logDir) {
            return $logDir;
        }

        switch ($this->buildType) {
            case 'hosted':
                    $logDir = implode(\DIRECTORY_SEPARATOR, ['', 'var', 'log', 'hittracker']);
                break;
            case 'electron':
                throw new UnexpectedValueException('"APP_LOG_DIR" env var must be set for Electron.');
                break;
            default:
                $logDir = implode(\DIRECTORY_SEPARATOR, [
                    $this->getProjectDir(),
                    'var', 'logs'
                ]);
        }

        return $logDir;
    }

    public function getTmpDir(): string
    {
        $tmpDir = self::getVarsFromEnv()['APP_TMP_DIR'];
        if ($tmpDir) {
            return $tmpDir;
        }

        switch ($this->buildType) {
            case 'electron':
                throw new UnexpectedValueException('"APP_TMP_DIR" env var must be set for Electron.');
                break;
            case 'hosted':
                if (!$tmpDir) {
                    $tmpDir = implode(\DIRECTORY_SEPARATOR,
                        ['', 'var', 'lib', 'hittracker', 'tmp', $this->environment]
                    );
                }
                break;
            default:
                $tmpDir = implode(\DIRECTORY_SEPARATOR, [
                    $this->getProjectDir(),
                    'var', 'tmp',
                    $this->environment,
                ]);
        }

        return $tmpDir;
    }

    protected function getKernelParameters()
    {
        $parameters = parent::GetKernelParameters();
        $parameters['kernel.tmp_dir'] = $this->getTmpDir();

        return $parameters;
    }
}
