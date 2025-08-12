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

namespace StingerSoft\EntitySearchBundle\Form;

use StingerSoft\EntitySearchBundle\Model\Query;
use StingerSoft\EntitySearchBundle\Model\Result\FacetSet;
use StingerSoft\EntitySearchBundle\Model\ResultSet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QueryType extends AbstractType {

	/**
	 *
	 * @var array
	 */
	protected array $defaultOptions = [];

	public function __construct(array $defaultOptions = []) {
		$this->defaultOptions = $defaultOptions;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Symfony\Component\Form\AbstractType::buildForm()
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->add('searchTerm', SearchType::class, array(
			'label' => 'stinger_soft_entity_search.forms.query.term.label'
		));

		$usedFacets = $options['used_facets'];
		$result = $options['result'];
		$preferredFilterChoices = $options['preferred_filter_choices'];
		$maxChoiceGroupCount = (int)$options['max_choice_group_count'];

		if($usedFacets && !$result) {
			$data = [];
			foreach($usedFacets as $facetType => $facetTypeOptions) {
				$facetTypeOptions = is_array($facetTypeOptions) ? $facetTypeOptions : [];
				$preferredChoices = $preferredFilterChoices[$facetType] ?? [];
				$i = 0;
				$builder->add('facet_' . $facetType, FacetType::class, array_merge(array(
					'label'             => 'stinger_soft_entity_search.forms.query.' . $facetType . '.label',
					'multiple'          => true,
					'expanded'          => true,
					'preferred_choices' => function($val) use ($preferredChoices, $data, $facetType, $maxChoiceGroupCount, &$i) {
						$facetKey = 'facet_' . $facetType;
						return $i++ < $maxChoiceGroupCount || $maxChoiceGroupCount === 0 || \in_array($val, $preferredChoices) || (isset($data[$facetKey]) && \in_array($val, $data[$facetKey]));
					}
				), $facetTypeOptions));
				unset($i);
			}
		}
		if($result) {
			$builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options, $result) {
				$this->createFacets($event->getForm(), $result->getFacets(), $options, $event->getData());
			});
		}

		$builder->add('filter', SubmitType::class, array(
			'label' => 'stinger_soft_entity_search.forms.query.filter.label'
		));
		$builder->add('clear', SubmitType::class, array(
			'label' => 'stinger_soft_entity_search.forms.query.clear.label'
		));
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Symfony\Component\Form\AbstractType::buildView()
	 */
	public function buildView(FormView $view, FormInterface $form, array $options): void {
		/**
		 *
		 * @var ResultSet $result
		 */
		$result = $options['result'];
		$view->vars['facetTypes'] = $result === null ? [] : array_keys($result->getFacets()->getFacets());
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \Symfony\Component\Form\AbstractType::configureOptions()
	 */
	public function configureOptions(OptionsResolver $resolver): void {
		$resolver->setDefault('data_class', Query::class);
		$resolver->setDefault('translation_domain', 'StingerSoftEntitySearchBundle');
		$resolver->setRequired('used_facets');
		$resolver->setDefault('result', null);

		$resolver->setRequired('preferred_filter_choices');
		$resolver->setDefault('preferred_filter_choices', $this->defaultOptions['preferred_filter_choices'] ?? []);

		$resolver->setRequired('max_choice_group_count');
		$resolver->setDefault('max_choice_group_count', $this->defaultOptions['max_choice_group_count'] ?? 10);

		$resolver->setDefault('facet_formatter', null);
		$resolver->setDefault('allow_extra_fields', true);
	}

	/**
	 *
	 * @param FormBuilderInterface|FormInterface $builder
	 * @param FacetSet                           $facets
	 * @param array                              $options
	 * @param Query                              $data
	 */
	protected function createFacets($builder, FacetSet $facets, array $options, Query $data): void {
		$preferredFilterChoices = $options['preferred_filter_choices'];
		$maxChoiceGroupCount = (int)$options['max_choice_group_count'];
		$selectedFacets = $data->getFacets();
		$usedFacets = $options['used_facets'];

		foreach($facets->getFacets() as $facetType => $facetValues) {
			$preferredChoices = $preferredFilterChoices[$facetType] ?? [];

			$i = 0;
			$facetTypeOptions = $usedFacets[$facetType] ?? [];
			$formatter = $options['facet_formatter'][$facetType] ?? null;
			$builder->add('facet_' . $facetType, FacetType::class, array_merge(array(
				'label'             => 'stinger_soft_entity_search.forms.query.' . $facetType . '.label',
				'multiple'          => true,
				'expanded'          => true,
				'choices'           => $this->generateFacetChoices($facetType, $facetValues, $selectedFacets[$facetType] ?? [], $formatter),
				'preferred_choices' => function($val) use ($preferredChoices, $selectedFacets, $facetType, $maxChoiceGroupCount, &$i) {
					return $i++ < $maxChoiceGroupCount || $maxChoiceGroupCount === 0 || \in_array($val, $preferredChoices) || (isset($selectedFacets[$facetType]) && \in_array($val, $selectedFacets[$facetType]));
				}
			), $facetTypeOptions));
			unset($i);
		}
	}

	/**
	 *
	 * @param string        $facetType
	 * @param array         $facets
	 * @param array         $selectedFacets
	 * @param callable|null $formatter
	 * @return array
	 */
	protected function generateFacetChoices(string $facetType, array $facets, array $selectedFacets = [], callable $formatter = null): array {
		$choices = [];
		$handledFacets = [];
		foreach($facets as $facet => $data) {
			$value = $data['value'];
			$count = $data['count'];
			if($count === 0 && !\in_array($facet, $selectedFacets)) {
				continue;
			}
			$handledFacets[$facet] = true;
			$choices[$this->formatFacet($formatter, $facetType, (string) $facet, $value, $count)] = (string) $facet;
		}
		foreach($selectedFacets as $facet) {
			if(!isset($facets[$facet]) || isset($handledFacets[$facet])) {
				continue;
			}
			$value = $facets[$facet]['value'];
			$count = 0;
			$choices[$this->formatFacet($formatter, $facetType, (string) $facet, $value, $count)] = (string) $facet;
		}
		return $choices;
	}

	/**
	 * @param $formatter
	 * @param $facetType
	 * @param $facet
	 * @param $value
	 * @param $count
	 * @return string
	 */
	protected function formatFacet(?callable $formatter, string $facetType, string $facet, $value, int $count): string {
		$default = $facet . ' (' . $count . ')';
		if(!$formatter) {
			return $default;
		}
		return $formatter($facetType, $value, $count, $default);

	}
}
