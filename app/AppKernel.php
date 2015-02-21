<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function __construct($environment, $debug)
    {
        if ('' === $this->getBuildType()) {
            $kernelList = join(',', array_values(AppKernelFactory::BUILD_TYPE_CLASSES));
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
            new HitTracker\CommonBundle\CommonBundle(),
            new HitTracker\GameBundle\HitTrackerGameBundle(),
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

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');

        $envParameters = $this->getEnvParameters();
        $loader->load(function($container) use($envParameters) {
            $container->getParameterBag()->add($envParameters);
        });
    }

    /**
     * @param bool $enabled
     */
    public function enableClassCache($enabled = false)
    {
        if ($enabled) $this->loadClassCache();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return join('/', [
            dirname($this->rootDir),
            '/var/cache',
            $this->getBuildType(),
            $this->environment
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return join('/', [
            dirname($this->rootDir),
            '/var/logs',
            $this->getBuildType()
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
