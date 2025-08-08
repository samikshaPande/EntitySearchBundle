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

use StingerSoft\EntitySearchBundle\Services\DoctrineListener;
use StingerSoft\EntitySearchBundle\Services\Mapping\EntityToDocumentMapperInterface;
use StingerSoft\EntitySearchBundle\Services\SearchService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * A Keen Metronic Admin Theme (http://www.keenthemes.com/) Bundle for Symfony2
 * with some additional libraries and PecPlatform specific customization.
 */
class StingerSoftEntitySearchExtension extends Extension {

	/**
	 *
	 * {@inheritDoc}
	 *
	 */
	public function load(array $configs, ContainerBuilder $container): void {
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$container->setParameter('stinger_soft.entity_search.available_facets', $config['facets']);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');
		
		$entityToDocumentMapperDefinition = $container->getDefinition(EntityToDocumentMapperInterface::class);
		$entityToDocumentMapperDefinition->setArgument('$mapping', $config['types']);
		
		$container->getDefinition(DoctrineListener::class)->setArgument('$enableIndexing', $config['enable_indexing']);
		$container->getDefinition(DoctrineListener::class)->setArgument('$enableAsync', $config['enable_async']);
		$container->setAlias(SearchService::class, $config['search_service']);

		$facetFormDefinition = $container->getDefinition('stinger_soft.entity_search.forms.query_type');
		$facetFormDefinition->addArgument($config['results']);
	}
}
