<?php
declare(strict_types=1);

/*
 * This file is part of the Stinger Entity Search package.
 *
 * (c) Oliver Kotte <oliver.kotte@stinger-soft.net>
 * (c) Florian Meyer <florian.meyer@stinger-soft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace StingerSoft\EntitySearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;


/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface {

	/**
	 *
	 * {@inheritDoc}
	 *
	 */
	public function getConfigTreeBuilder(): TreeBuilder {
		$treeBuilder = new TreeBuilder('stinger_soft_entity_search');
		// @formatter:off
		$treeBuilder->getRootNode()->children()
			->booleanNode('enable_indexing')->defaultFalse()->end()
			->booleanNode('enable_search')->defaultFalse()->end()
			->booleanNode('enable_async')->defaultFalse()->end()
			->scalarNode('search_service')->defaultValue('stinger_soft.entity_search.dummy_search_service')->end()
			->arrayNode('results')->addDefaultsIfNotSet()
				->children()
					->integerNode('max_choice_group_count')->defaultValue(10)->end()
					->arrayNode('preferred_type_choices')
						->prototype('scalar')->end()
					->end()
					->arrayNode('preferred_filetype_choices')
						->prototype('scalar')->end()
					->end()
					->arrayNode('ingore_patterns')
						->prototype('scalar')->end()
					->end()
				->end()
			->end()
			->arrayNode('facets')->defaultValue(array('stinger_soft_entity_search.facets.author', 'stinger_soft_entity_search.facets.editors', 'stinger_soft_entity_search.facets.type'))
				->prototype('scalar')->end()
			->end()
			->arrayNode('types')
				->useAttributeAsKey('name')
				->prototype('array')
					->children()
						->arrayNode('mappings')
							->useAttributeAsKey('name')
							->cannotBeEmpty()
							->prototype('array')
								->children()
									->scalarNode('propertyPath')->defaultValue(false)->end()
								->end()
							->end()
						->end()
						->arrayNode('persistence')
							->children()
								->scalarNode('model')->end()
							->end()
						->end()
					->end()
				->end()
			->end();
		// @formatter:on
		
		return $treeBuilder;
	}
}
