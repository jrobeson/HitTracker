<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
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
            $kernelList = implode(',', array_values(AppKernelFactory::BUILD_TYPE_CLASSES));
            throw new \InvalidValueException(
                sprintf('AppKernel must be instantiated as one of: %s', $kernelList)
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
            new Elnur\Bundle\BootstrapBundle\ElnurBootstrapBundle(),
            new Nelmio\SecurityBundle\NelmioSecurityBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Kunstmaan\LiveReloadBundle\KunstmaanLiveReloadBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
        }

        return $bundles;
    }

    private function getConfigFiles($environment, $buildType)
    {
        $configFiles = [
            'parameters_default.yml',
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
        $loader->load(function ($container) use ($envParameters) {
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

    /** @return string */
    private function getBuildType()
    {
        return strtolower(str_replace('AppKernel', '', get_class($this)));
    }
}

class AppKernelFactory
{
    const BUILD_TYPE_CLASSES = [
        'hosted' => 'HostedAppKernel',
        'standalone' => 'StandaloneAppKernel',
    ];

    private function __construct()
    {
    }

    /**
     * Build an AppKernel from self::BUILD_TYPE_CLASSES
     *
     * @param string $buildType
     * @param string $environment
     * @param bool   $debug
     * @see AppKernel::__construct for argument explanations
     */
    public static function get($buildType, $environment, $debug = false)
    {
        if (!self::has($buildType)) {
            throw new \InvalidArgumentException('No such buildType exists.');
        }
        $kernel = self::BUILD_TYPE_CLASSES[$buildType];
        $kernelFile = $kernel.'.php';
        if (!file_exists(__DIR__.'/'.$kernelFile)) {
            throw new \RuntimeException(sprintf('Kernel "%s" does not exist', $kernelFile));
        }
        require_once __DIR__.'/'.$kernel.'.php';

        return new $kernel($environment, $debug);
    }

    /** @param string $buildType */
    public static function has($buildType)
    {
        return in_array($buildType, array_keys(self::BUILD_TYPE_CLASSES));
    }
}
