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

namespace StingerSoft\EntitySearchBundle\Services;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use StingerSoft\EntitySearchBundle\Events\DocumentPreSaveEvent;
use StingerSoft\EntitySearchBundle\Model\SearchableAlias;
use StingerSoft\EntitySearchBundle\Services\Mapping\EntityToDocumentMapperInterface;
use StingerSoft\EntitySearchBundle\Services\Messenger\RemoveDocumentMessage;
use StingerSoft\EntitySearchBundle\Services\Messenger\SaveDocumentMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DoctrineListener implements EventSubscriber {

	/**
	 * @var bool
	 */
	protected bool $needsFlush = false;


	/**
	 * @var EventDispatcherInterface|null
	 */
	protected ?EventDispatcherInterface $eventDispatcher = null;

	/**
	 * DoctrineListener constructor.
	 *
	 * @param EntityToDocumentMapperInterface $entityToDocumentMapper
	 * @param SearchService                   $searchService
	 * @param MessageBusInterface|null        $bus
	 * @param bool                            $enableIndexing
	 * @param bool                            $enableAsync
	 */
	public function __construct(protected readonly EntityToDocumentMapperInterface $entityToDocumentMapper, protected readonly SearchService $searchService, protected readonly ?MessageBusInterface $bus, protected bool $enableIndexing = false, protected bool $enableAsync = false) {
	}

	/**
	 * @return array|string[]
	 */
	public function getSubscribedEvents(): array {
		return array(
			'postPersist',
			'postUpdate',
			'preRemove',
			'postFlush'
		);
	}

	/**
	 * @return EventDispatcherInterface|null
	 */
	public function getEventDispatcher(): ?EventDispatcherInterface {
		return $this->eventDispatcher;
	}

	/**
	 * @param EventDispatcherInterface|null $eventDispatcher
	 * @required
	 */
	public function setEventDispatcher(EventDispatcherInterface $eventDispatcher = null): void {
		$this->eventDispatcher = $eventDispatcher;
	}


	/**
	 *
	 */
	public function enableIndexing(): void {
		$this->enableIndexing = true;
	}

	/**
	 *
	 */
	public function disableIndexing(): void {
		$this->enableIndexing = false;
	}

	/**
	 * @return bool
	 */
	public function isIndexingEnabled(): bool {
		return $this->enableIndexing;
	}

	/**
	 * Index the entity after it is persisted for the first time
	 *
	 * @param LifecycleEventArgs $args
	 */
	public function postPersist(LifecycleEventArgs $args): void {
		if (!$this->enableIndexing) {
			return;
		}
		$this->updateEntity($args->getObject(), $args->getObjectManager());
	}

	/**
	 * @param PostFlushEventArgs $eventArgs
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function postFlush(PostFlushEventArgs $eventArgs): void {
		if ($this->needsFlush) {
			$this->needsFlush = false;
			$eventArgs->getEntityManager()->flush();
		}
	}

	/**
	 * Removes the entity from the index when it marked for deletion
	 *
	 * @param LifecycleEventArgs $args
	 */
	public function preRemove(LifecycleEventArgs $args): void {
		if (!$this->enableIndexing) {
			return;
		}
		$this->removeEntity($args->getObject(), $args->getObjectManager());
	}

	/**
	 * Updates the entity in the index after it is updated
	 *
	 * @param LifecycleEventArgs $args
	 */
	public function postUpdate(LifecycleEventArgs $args): void {
		if (!$this->enableIndexing) return;
		$this->updateEntity($args->getObject(), $args->getObjectManager());
	}

	/**
	 *
	 * @return EntityToDocumentMapperInterface
	 */
	protected function getEntityToDocumentMapper(): EntityToDocumentMapperInterface {
		return $this->entityToDocumentMapper;
	}

	/**
	 *
	 * @return SearchService
	 */
	protected function getSearchService(): SearchService {
		return $this->searchService;
	}

	/**
	 *
	 * @param object $entity
	 */
	protected function updateEntity(object $entity, ObjectManager $manager): void {
		if ($entity instanceof SearchableAlias) {
			$entity = $entity->getEntityToIndex();
		}
		$document = $this->getEntityToDocumentMapper()->createDocument($manager, $entity);
		if ($document !== null) {
			if ($this->getEventDispatcher()) {
				$event = new DocumentPreSaveEvent($document);
				$this->getEventDispatcher()->dispatch($event, DocumentPreSaveEvent::NAME);
			}
			if($this->enableAsync) {
				$this->bus->dispatch(new SaveDocumentMessage($document));
				return;
			}
			$this->getSearchService()->saveDocument($document);
			$this->needsFlush = true;
		}
	}

	/**
	 *
	 * @param object $entity
	 */
	protected function removeEntity(object $entity, ObjectManager $manager): void {
		if ($entity instanceof SearchableAlias) {
			$entity = $entity->getEntityToIndex();
		}
		$document = $this->getEntityToDocumentMapper()->createDocument($manager, $entity);
		if ($document !== null) {
			if($this->enableAsync) {
				$this->bus->dispatch(new RemoveDocumentMessage($document));
				return;
			}
			$this->getSearchService()->removeDocument($document);
			$this->needsFlush = true;
		}
	}
}
