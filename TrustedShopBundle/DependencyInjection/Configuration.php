<?php

namespace Gamma\TrustedShop\TrustedShopBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gamma_trusted_shop');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
		$rootNode
            ->children()
				->scalarNode('scheme')
					->defaultValue('http')
					->cannotBeEmpty()
				->end()
				->scalarNode('host')
					->defaultValue('www.trustedshops.com')
					->cannotBeEmpty()
				->end()
				->scalarNode('path')
					->defaultValue('api/')
					->cannotBeEmpty()
				->end()
				->scalarNode('api_version')
					->defaultValue('1')
					->cannotBeEmpty()
				->end()
				->scalarNode('maxRank')
					->defaultValue('5')
					->cannotBeEmpty()
				->end()            
				->scalarNode('TSID')
					->isRequired()
				->end()
			->end()
        ;
        
        return $treeBuilder;
    }
}
