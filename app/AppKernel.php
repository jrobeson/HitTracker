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
        $isProd = 'prod' == $environment;
        if (!$isProd && $debug) {
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
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\AopBundle\JMSAopBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new HitTracker\CommonBundle\CommonBundle(),
            new HitTracker\GameBundle\HitTrackerGameBundle(),
            new C33s\StaticPageContentBundle\C33sStaticPageContentBundle(),
            new Elnur\Bundle\BootstrapBundle\ElnurBootstrapBundle(),
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

        $localFile = __DIR__.'/config/local_'.$this->getEnvironment().'.yml';

        if (is_file($localFile)) {
            $loader->load($localFile);
        }

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
        return dirname($this->rootDir).'/var/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return dirname($this->rootDir).'/var/logs';
    }
}
