<?php

/**
 * Joby's PHP Toolbox: https://code.byjoby.com/php-toolbox/
 * MIT License: Copyright (c) 2024 Joby Elliott
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
 * SOFTWARE.
 */

namespace Joby\Toolbox\Ranges;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Joby\Toolbox\Sorting\Sorter;

/**
 * @template T of AbstractRange
 * @implements ArrayAccess<int,T>
 * @implements IteratorAggregate<int,T>
 */
class RangeCollection implements Countable, ArrayAccess, IteratorAggregate
{
    protected string $class;
    /** @var T[] */
    protected $ranges = [];

    /**
     * @template RangeType of AbstractRange
     * @param RangeType $range 
     * @param RangeType ...$ranges 
     * @return RangeCollection<RangeType>
     */
    public static function create(AbstractRange $range, AbstractRange ...$ranges): RangeCollection
    {
        return new RangeCollection($range::class, $range, ...$ranges);
    }

    /**
     * @template RangeType of AbstractRange
     * @param RangeType|class-string<RangeType> $class 
     * @return RangeCollection<RangeType>
     */
    public static function createEmpty(AbstractRange|string $class): RangeCollection
    {
        if (is_object($class)) return new RangeCollection($class::class);
        else return new RangeCollection($class);
    }

    /**
     * @return T[]
     */
    public function toArray(): array
    {
        return $this->ranges;
    }

    /**
     * @param T ...$ranges
     */
    public function add(AbstractRange ...$ranges): static
    {
        foreach ($ranges as $range) {
            if (!($range instanceof $this->class)) {
                throw new InvalidArgumentException("Ranges must be of type $this->class");
            }
        }
        $this->ranges = array_merge($this->ranges, $ranges);
        $this->sort();
        return $this;
    }

    /**
     * @param class-string<T> $class
     * @param T ...$ranges 
     * @return void 
     */
    protected function __construct(string $class, AbstractRange ...$ranges)
    {
        $this->class = $class;
        $this->add(...$ranges);
    }

    protected function sort(): void
    {
        static $sorter;
        $sorter = $sorter ?? $sorter = new Sorter(
            fn (AbstractRange $a, AbstractRange $b): int => $a->startAsNumber() <=> $b->startAsNumber(),
            fn (AbstractRange $a, AbstractRange $b): int => $a->endAsNumber() <=> $b->endAsNumber(),
        );
        $sorter->sort($this->ranges);
    }

    public function count(): int
    {
        return count($this->ranges);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->ranges[$offset]);
    }

    /**
     * @param int $offset
     * @return T|null
     */
    public function offsetGet($offset): ?AbstractRange
    {
        return $this->ranges[$offset] ?? null;
    }

    /**
     * @param int|null $offset
     * @param T $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) $this->add($value);
        else {
            if (!($value instanceof $this->class)) {
                throw new InvalidArgumentException("Ranges must be of type $this->class");
            }
            $this->ranges[$offset] = $value;
            $this->sort();
        }
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->ranges[$offset]);
    }

    /**
     * @return ArrayIterator<int,T>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->ranges);
    }
}
