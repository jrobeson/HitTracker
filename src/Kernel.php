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

    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new \WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new \winzou\Bundle\StateMachineBundle\winzouStateMachineBundle(),
            new \Sylius\Bundle\UiBundle\SyliusUiBundle(),
            new \Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new \Sylius\Bundle\SettingsBundle\SyliusSettingsBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),
            new \LazerBall\HitTracker\PubSubBundle\PubSubBundle(),
            new \LazerBall\HitTracker\GameBundle\HitTrackerGameBundle(),
            new \C33s\StaticPageContentBundle\C33sStaticPageContentBundle(),
            new \Nelmio\SecurityBundle\NelmioSecurityBundle(),
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new \Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
            new \Dunglas\DoctrineJsonOdm\Bundle\DunglasDoctrineJsonOdmBundle(),
            new \Incenteev\HashedAssetBundle\IncenteevHashedAssetBundle(),
        ];

        if (in_array($this->getEnvironment(), ['development', 'test'])) {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    protected function getBuildType(): string
    {
        return $this->buildType;
    }

    private function getConfigFiles($environment, $buildType): array
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
     * Force root directory to be %kernel.project_dir/app, so templates and translations
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
        switch ($this->buildType) {
            case 'electron':
                $varDir = env('SYMFONY__VAR_DIR');
                if (!$varDir) {
                    throw new UnexpectedValueException('"SYMFONY__VAR_DIR" env var must be set for Electron.');
                }
                $cacheDir = implode(DIRECTORY_SEPARATOR, [$varDir, 'cache']);
                break;
            case 'hosted':
                $cacheDir = implode(DIRECTORY_SEPARATOR, ['', 'var', 'lib', 'hittracker', $this->environment]);
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
        switch ($this->buildType) {
            case 'electron':
                $varDir = env('SYMFONY__VAR_DIR');
                if (!$varDir) {
                    throw new UnexpectedValueException('"SYMFONY__VAR_DIR" env var must be set for Electron.');
                }
                $logDir = implode(DIRECTORY_SEPARATOR, [$varDir, 'log']);
                break;
            case 'hosted':
                $logDir = implode('/', ['', 'var', 'log', 'hittracker']);
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

        return $kernelParameters;
    }
}
