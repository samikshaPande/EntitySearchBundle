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

namespace StingerSoft\EntitySearchBundle\Model;

class DocumentAdapter implements Document {

	/**
	 * Fieldnames which are forced to be single valued
	 * @var array
	 */
	public static $forceSingleValueFields = [
		Document::FIELD_AUTHOR,
		Document::FIELD_LAST_MODIFIED,
		Document::FIELD_TYPE,
		Document::FIELD_NO_ICON_TYPE
	];

	/**
	 * Stores are fields with their values which should be stored in the index
	 *
	 * @var array
	 */
	protected array $fields = [];

	/**
	 *
	 * @var string
	 */
	protected ?string $entityClass = null;

	/**
	 *
	 * @var mixed
	 */
	protected $entityId = null;

	/**
	 *
	 * @var string
	 */
	protected ?string $entityType = null;

	/**
	 * @var string|null
	 */
	protected ?string $noIconEntityType = null;

	/**
	 *
	 * @var string
	 */
	protected ?string $file = null;

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::addField()
	 */
	public function addField(string $fieldName, $value): void {
		$this->fields[$fieldName] = $value;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::getFields()
	 */
	public function getFields(): array {
		return $this->fields;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::getFieldValue()
	 */
	public function getFieldValue(string $fieldName) {
		$value = $this->fields[$fieldName] ?? null;
		if(\in_array($fieldName, self::$forceSingleValueFields) && \is_array($value)) {
			return current($value);
		}
		return $value;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::addMultiValueField()
	 */
	public function addMultiValueField(string $fieldName, $value): void {
		if(!array_key_exists($fieldName, $this->fields)) {
			$this->fields[$fieldName] = array(
				$value
			);
		} else if(!\is_array($this->fields[$fieldName])) {
			$this->fields[$fieldName] = array(
				$value, $this->fields[$fieldName]
			);
		} else if(!\in_array($value, $this->fields[$fieldName])) {
			$this->fields[$fieldName][] = $value;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::getEntityClass()
	 */
	public function getEntityClass(): string {
		return $this->entityClass;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::setEntityClass()
	 */
	public function setEntityClass(string $clazz): void {
		$this->entityClass = $clazz;
		if(!$this->entityType) {
			$this->entityType = $clazz;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::getEntityId()
	 */
	public function getEntityId() {
		return $this->entityId;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::setEntityId()
	 */
	public function setEntityId($id): void {
		$this->entityId = $id;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::getEntityType()
	 */
	public function getEntityType(): string {
		return $this->entityType ? $this->entityType : $this->getEntityClass();
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::setEntityType()
	 */
	public function setEntityType(string $type): void {
		$this->entityType = $type;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::getFile()
	 */
	public function getFile(): ?string {
		return $this->file;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::setFile()
	 */
	public function setFile(string $path): void {
		$this->file = $path;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::__get()
	 */
	public function __get(string $name) {
		return $this->getFieldValue($name);
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \StingerSoft\EntitySearchBundle\Model\Document::__isset()
	 */
	public function __isset(string $name): bool {
		return $this->getFieldValue($name) !== null;
	}
}
