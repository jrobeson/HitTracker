<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

abstract class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function __construct($environment, $debug)
    {
        /**
         * Can't add new args to the constructor, due to instantiation
         * in the cache warmer
         * @see Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand::warmup()
         */
        if ('' === $this->getBuildType()) {
            throw new InvalidArgumentException(
                'AppKernel can\'t be instantiated directly.
                You must call one of the subclasses.'
            );
        }

        $isProd = 'prod' == $environment;
        if (!$isProd && $debug) { /** @todo check this */
            Symfony\Component\Debug\Debug::enable();
        }

        parent::__construct($environment, $debug);

        $this->enableClassCache($isProd);
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new Sylius\Bundle\SettingsBundle\SyliusSettingsBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new LazerBall\HitTracker\CommonBundle\CommonBundle(),
            new LazerBall\HitTracker\GameBundle\HitTrackerGameBundle(),
            new C33s\StaticPageContentBundle\C33sStaticPageContentBundle(),
            new Nelmio\SecurityBundle\NelmioSecurityBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
        }

        return $bundles;
    }

    private function getConfigFiles($environment, $buildType)
    {
        $configFiles = [
            'parameters_default.yml',
            sprintf('parameters_%s.yml', $buildType),
            'config.yml',
            sprintf('config_%s.yml', $buildType),
            sprintf('config_%s.yml', $environment),
            sprintf('config_%s_%s.yml', $buildType, $environment),
        ];
        if ('test' == $environment) { // test requires dev files first
            array_splice($configFiles, 2, 0, [
                'config_dev.yml',
                sprintf('config_%s_dev.yml', $buildType),
            ]);
        }

        $configFiles = array_map(function ($fileName) {
            return sprintf('%s/config/%s', __DIR__, $fileName);
        }, $configFiles);

        return $configFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configFiles = $this->getConfigFiles($this->getEnvironment(), $this->getBuildType());

        foreach ($configFiles as $configFile) {
            if (file_exists($configFile)) {
                $loader->load($configFile);
            }
        }

        $envParameters = $this->getEnvParameters();
        $loader->load(function (ContainerInterface $container) use ($envParameters) {
            $container->getParameterBag()->add($envParameters);
        });
    }

    /**
     * @param bool $enabled
     */
    public function enableClassCache($enabled = false)
    {
        if ($enabled) {
            $this->loadClassCache();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return implode('/', [
            dirname($this->rootDir),
            '/var/cache',
            $this->getBuildType(),
            $this->environment,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return implode('/', [
            dirname($this->rootDir),
            '/var/logs',
            $this->getBuildType(),
        ]);
    }
}