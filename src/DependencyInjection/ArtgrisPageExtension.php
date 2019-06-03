<?php

namespace Artgris\Bundle\PageBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ArtgrisPageExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = $this->processConfigFiles($configs);
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('artgrispage.config', $config);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yaml');
        $loader->load('extensions.yaml');
    }

    private function processConfigFiles(array $configs)
    {
        foreach ($configs as $i => $config) {
            if (\array_key_exists('types', $config)) {
                foreach ($config['types'] as $types) {
                    foreach ($types as $key => $typeName) {
                        if (!\class_exists($typeName)) {
                            throw new \RuntimeException(\sprintf('Type "%s" not found.', $typeName));
                        }
                    }
                }
            }
        }

        return $configs;
    }
}
