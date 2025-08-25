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

interface Document {

	/**
	 * Key of the index field <em>title</em>
	 *
	 * @var string
	 */
	public const FIELD_TITLE = 'title';

	/**
	 * Key of the index field <em>content</em>
	 *
	 * @var string
	 */
	public const FIELD_CONTENT = 'content';

	/**
	 * Key of the index field <em>last_modified</em>
	 *
	 * @var string
	 */
	public const FIELD_LAST_MODIFIED = 'last_modified';

	/**
	 * Key of the index field <em>author</em>
	 *
	 * @var string
	 */
	public const FIELD_AUTHOR = 'author';

	/**
	 * Key of the index field <em>keywords</em>
	 *
	 * @var string
	 */
	public const FIELD_KEYWORDS = 'keywords';

	/**
	 * Key of the index field <em>roles</em>
	 *
	 * @var string
	 */
	public const FIELD_ROLES = 'roles';

	/**
	 * Key of the index field <em>editors</em>
	 *
	 * @var string
	 */
	public const FIELD_EDITORS = 'editors';

	/**
	 * Key of the index field <em>deleted_at</em> If set the entity will only appear in the search results until this date
	 *
	 * @var string
	 */
	public const FIELD_DELETED_AT = 'deleted_at';

	/**
	 * Key of the index field <em>type</em>
	 *
	 * You should avoid using it!!
	 *
	 * @var string
	 */
	public const FIELD_TYPE = 'type';

	/**
	 * Key of the index field <em>file type</em> containing the mimetype of the file if available
	 *
	 * @var string
	 */
	public const FIELD_CONTENT_TYPE = 'Content-Type';

	/**
	 * Key of the index field <em>no-icon-type</em>
	 *
	 * You should avoid using it!!
	 *
	 * @var string
	 */
	public const FIELD_NO_ICON_TYPE = 'noIconType';


	/**
	 * Adds a field and its value to the index
	 *
	 * @param string $fieldName
	 *            The name of the field
	 * @param mixed $value
	 *            The value of this field
	 */
	public function addField(string $fieldName, $value): void;

	/**
	 * Returns all field saved in this document
	 *
	 * @return array
	 * @internal
	 *
	 */
	public function getFields(): array;

	/**
	 * Returns all values for the given field
	 *
	 * @param string $fieldName
	 *            The name of the field
	 * @return mixed|mixed[] The stored values of this field. <code>NULL</code> if no value exists
	 */
	public function getFieldValue(string $fieldName);

	/**
	 * Adds a value to a field without deleting the already existing values
	 *
	 * @param string $fieldName
	 *            The name of the field
	 * @param mixed $value
	 *            The additional value of this field
	 */
	public function addMultiValueField(string $fieldName, $value): void;

	/**
	 * Set the class name of the corresponding entity.
	 * This method will automatically be called by the doctrine event listener
	 *
	 * @internal
	 *
	 * @param string $clazz
	 *            The classname
	 */
	public function setEntityClass(string $clazz): void;

	/**
	 * Get the classname of the corresponding entity
	 *
	 * @return string The classname of the corresponding entity
	 */
	public function getEntityClass(): string;

	/**
	 * Set the ID of the corresponding entity.
	 *
	 * This method will automatically be called by the doctrine event listener
	 *
	 * @internal
	 *
	 * @param mixed $id
	 *            The ID of the corresponding entity
	 */
	public function setEntityId($id): void;

	/**
	 * Get the ID of the corresponding entity
	 *
	 * @return mixed The ID of the corresponding entity
	 */
	public function getEntityId();

	/**
	 * Sets the <em>type</em> of this entity.
	 * This property is used to group multiple subclasses into one virtual entity type,
	 * hiding some programatically needed complexity from the user
	 *
	 * @param string $type
	 */
	public function setEntityType(string $type): void;

	/**
	 * Gets the <em>type</em> of this entity.
	 * This property is used to group multiple subclasses into one virtual entity type,
	 * hiding some programatically needed complexity from the user.
	 *
	 * If no entity type is set, the class will be used instead
	 *
	 * @param string $type
	 */
	public function getEntityType(): string;

	/**
	 * Sets a file to be indexed
	 *
	 * <strong>note:</strong> This may not supported by the underlying implementation
	 *
	 * @param string $path
	 */
	public function setFile(string $path): void;

	/**
	 * Gets the file to be indexed
	 *
	 * <strong>note:</strong> This may not supported by the underlying implementation
	 *
	 * @return string
	 */
	public function getFile(): ?string;

	/**
	 * Gets the given field by its name
	 *
	 * @param string $name
	 * @return mixed|mixed[]
	 */
	public function __get(string $name);

	/**
	 * Checks whether the given field is set or not
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function __isset(string $name) : bool;
}
