<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

use ArrayAccess;
use ICanBoogie\ToArray;
use IteratorAggregate;
use Traversable;

use function str_starts_with;
use function strlen;

/**
 * Custom data attributes are intended to store custom data private to the page or application,
 * for which there are no more appropriate attributes or elements.
 *
 * @see http://www.w3.org/TR/html5/elements.html#embedding-custom-non-visible-data-with-the-data-attributes
 *
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<string, mixed>
 */
final class Dataset implements ArrayAccess, IteratorAggregate, ToArray
{
    private const ATTRIBUTE_PREFIX = 'data-';

    private static function to_attribute(string $property): string
    {
        return self::ATTRIBUTE_PREFIX . $property;
    }

    private static function to_property(string $attribute): string
    {
        return substr($attribute, strlen(self::ATTRIBUTE_PREFIX));
    }

    /**
     * @param Element $element The target element.
     * @param array<string, mixed> $properties The initial properties of the dataset.
     */
    public function __construct(
        private Element $element,
        array $properties = []
    ) {
        foreach ($properties as $property => $value) {
            $this[$property] = $value;
        }
    }

    /**
     * Sets the value of a property.
     *
     * The attribute corresponding to the property is set.
     *
     * @param string $offset An attribute.
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->element->offsetSet(self::to_attribute($offset), $value);
    }

    /**
     * Returns the value of a data attribute.
     *
     * The value is read from the attribute corresponding to the property.
     *
     * @param string $offset
     */
    public function offsetGet($offset): mixed
    {
        return $this->element->offsetGet(self::to_attribute($offset));
    }

    /**
     * Checks if a data attribute exists.
     *
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->element->offsetExists(self::to_attribute($offset));
    }

    /**
     * Remove a data attribute.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->element->offsetUnset(self::to_attribute($offset));
    }

    /**
     * Iterate over the data attributes.
     *
     * @return Traversable<string, mixed>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->element->attributes as $attribute => $value) {
            if (str_starts_with($attribute, self::ATTRIBUTE_PREFIX)) {
                yield self::to_property($attribute) => $value;
            }
        }
    }

    /**
     * Returns an array representation of the dataset.
     *
     * @return array<string, mixed>
     */
    public function to_array(): array
    {
        $properties = [];

        foreach ($this->element->attributes as $attribute => $value) {
            if (!str_starts_with($attribute, self::ATTRIBUTE_PREFIX)) {
                continue;
            }

            $properties[self::to_property($attribute)] = $value;
        }

        return $properties;
    }
}
