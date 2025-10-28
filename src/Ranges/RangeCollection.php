<?php

/**
 * Joby's PHP Toolbox: https://go.joby.lol/phptoolbox
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
use Stringable;

/**
 * Stores a collection of ranges, which must be all of the same type, and
 * ensures that they are always sorted in ascencing chronological order. Also
 * provides a number of methods for manipulating the colleciton, such as merging
 * it with another collection, mapping, filtering, boolean operations with other
 * collections or ranges, etc.
 *
 * It also allows counting, array access, and iteration in foreach loops.
 *
 * @template T of AbstractRange
 * @implements ArrayAccess<int,T>
 * @implements IteratorAggregate<int,T>
 */
class RangeCollection implements Countable, ArrayAccess, IteratorAggregate, Stringable
{
    /** @var class-string<T> $class */
    protected string $class;
    /** @var array<int,T> $ranges */
    protected $ranges = [];

    /**
     * @param class-string<T> $class
     * @param T               ...$ranges
     *
     * @return void
     */
    protected function __construct(string $class, AbstractRange ...$ranges)
    {
        foreach ($ranges as $range) {
            if (!($range instanceof $class)) {
                throw new InvalidArgumentException("Ranges must be of type $class");
            }
        }
        $this->class = $class;
        $this->ranges = array_values($ranges);
        $this->sort();
    }

    /**
     * Create a new collection from any number of ranges. Must have at least one
     * argument, which is used to determine the type of range to store.
     *
     * @template RangeType of AbstractRange
     * @param RangeType $range
     * @param RangeType ...$ranges
     *
     * @return RangeCollection<RangeType>
     */
    public static function create(AbstractRange $range, AbstractRange ...$ranges): RangeCollection
    {
        return new RangeCollection($range::class, $range, ...$ranges);
    }

    /**
     * Create an empty collection of a specific range type.
     *
     * @template RangeType of AbstractRange
     * @param RangeType|class-string<RangeType> $class
     *
     * @return RangeCollection<RangeType>
     */
    public static function createEmpty(AbstractRange|string $class): RangeCollection
    {
        if (is_object($class)) return new RangeCollection($class::class);
        else return new RangeCollection($class);
    }

    /**
     * Subtract the given range from all ranges in this collection.
     *
     * @param T $other
     *
     * @return RangeCollection<T>
     */
    public function booleanNot(AbstractRange $other): RangeCollection
    {
        return $this->map(function (AbstractRange $range) use ($other) {
            return $range->booleanNot($other);
        });
    }

    /**
     * Return only the ranges that intersect with the given range from any
     * range in this colelction.
     *
     * @param T $other
     *
     * @return RangeCollection<T>
     */
    public function booleanAnd(AbstractRange $other): RangeCollection
    {
        return $this->map(function (AbstractRange $range) use ($other) {
            return $range->booleanAnd($other);
        });
    }

    /**
     * Transform this collection into the set of ranges that fully contain all
     * ranges in this collection. This is done by merging overlapping or adjacent
     * ranges until no more merges are possible.
     *
     * @return RangeCollection<T>
     */
    public function mergeRanges(): RangeCollection
    {
        return $this
            ->mergeIntersectingRanges()
            ->mergeAdjacentRanges();
    }

    /**
     * Merge all ranges that intersect with each other into single continuous
     * ranges instead of a buch of separate chunks.
     *
     * @return RangeCollection<T>
     */
    public function mergeIntersectingRanges(): RangeCollection
    {
        $merged = [];
        foreach ($this->ranges as $range) {
            $found = false;
            foreach ($merged as $k => $m) {
                if ($range->intersects($m)) {
                    $found = true;
                    $v = $m->booleanOr($range)[0];
                    assert($v instanceof $this->class);
                    $merged[$k] = $v;
                    break;
                }
            }
            if (!$found) $merged[] = $range;
        }
        return new RangeCollection($this->class, ...$merged);
    }

    /**
     * Filter this collection to only include ranges that return true when
     * passed to the provided callback.
     *
     * @param callable(T):bool $callback
     *
     * @return RangeCollection<T>
     */
    public function filter(callable $callback): RangeCollection
    {
        return new RangeCollection($this->class, ...array_filter($this->ranges, $callback));
    }

    /**
     * Inspect and modify each range in the collection using the provided callback. If the callback returns a
     * collection it will be merged. If the callback returns null, the range will be removed.
     *
     * @param callable(T):(T|RangeCollection<T>|null) $callback
     *
     * @return RangeCollection<T>
     */
    public function map(callable $callback): RangeCollection
    {
        $new_ranges = [];
        foreach ($this->ranges as $range) {
            $new_range = $callback($range);
            if ($new_range instanceof RangeCollection) {
                $new_ranges = array_merge($new_ranges, $new_range->toArray());
            } elseif ($new_range !== null) {
                $new_ranges[] = $new_range;
            }
        }
        return new RangeCollection($this->class, ...$new_ranges);
    }

    public function isEmpty(): bool
    {
        return empty($this->ranges);
    }

    /**
     * @return array<int,T>
     */
    public function toArray(): array
    {
        return $this->ranges;
    }

    /**
     * @param T ...$ranges
     *
     * @return RangeCollection<T>
     */
    public function add(AbstractRange ...$ranges): RangeCollection
    {
        $ranges = array_merge($this->ranges, $ranges);
        return new RangeCollection($this->class, ...$ranges);
    }

    /**
     * Return the highest value in the collection, if the collection is not empty.
     *
     * @return T|null
     */
    public function end(): mixed
    {
        if (!isset($this->ranges[count($this->ranges) - 1])) return null;
        // @phpstan-ignore-next-line this definitely returns the right type
        return $this->ranges[count($this->ranges) - 1]->end();
    }

    /**
     * Return the lowest value in the collection, if the collection is not empty.
     *
     * @return T|null
     */
    public function start(): mixed
    {
        if (!isset($this->ranges[0])) return null;
        // @phpstan-ignore-next-line this definitely returns the right type
        return $this->ranges[0]->start();
    }

    /**
     * Return the highest value in the collection, if the collection is not empty, as a number.
     *
     * @return int|null
     */
    public function endAsNumber(): mixed
    {
        $i = count($this->ranges) - 1;
        if (!isset($this->ranges[$i])) return null;
        // @phpstan-ignore-next-line this definitely returns the right type
        return $this->ranges[$i]->endAsNumber();
    }

    /**
     * Return the lowest value in the collection, if the collection is not empty, as a number.
     *
     * @return int|null
     */
    public function startAsNumber(): mixed
    {
        if (!isset($this->ranges[0])) return null;
        // @phpstan-ignore-next-line this definitely returns the right type
        return $this->ranges[0]->startAsNumber();
    }

    public function count(): int
    {
        return count($this->ranges);
    }

    /**
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->ranges[$offset]);
    }

    /**
     * @param int $offset
     *
     * @return T|null
     */
    public function offsetGet($offset): ?AbstractRange
    {
        return $this->ranges[$offset] ?? null;
    }

    /**
     * @param int|null $offset
     * @param T        $value
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

    public function __toString(): string
    {
        return implode(', ', $this->ranges);
    }

    /**
     * Merge all ranges that are adjacent to each other into single continuous
     * ranges instead of a buch of separate chunks. Note that this does not
     * merge ranges that overlap, and strictly merges only ranges that are
     * adjacent. If ranges are adjacent to multiple other ranges only one will
     * be merged, and the others will remain separate. This method is protected
     * because its behavior is complex in the case of multiple adjacent ranges
     * and most users are probably looking for the behavior of mergeRanges()
     * or mergeIntersectingRanges() anyway.
     *
     * @return RangeCollection<T>
     */
    protected function mergeAdjacentRanges(): RangeCollection
    {
        /** @var array<int,T> $merged */
        $merged = [];
        foreach ($this->ranges as $range) {
            $found = false;
            /** @var T $m */
            foreach ($merged as $k => $m) {
                if ($range->adjacent($m)) {
                    $found = true;
                    $v = $m->booleanOr($range)[0];
                    assert($v instanceof $this->class);
                    $merged[$k] = $v;
                    break;
                }
            }
            if (!$found) $merged[] = $range;
        }
        return new RangeCollection($this->class, ...$merged);
    }

    protected function sort(): void
    {
        /** @var Sorter|null $sorter */
        static $sorter = null;
        if (is_null($sorter)) {
            $sorter = new Sorter(
                fn(AbstractRange $a, AbstractRange $b): int => $a->startAsNumber() <=> $b->startAsNumber(),
                fn(AbstractRange $a, AbstractRange $b): int => $a->endAsNumber() <=> $b->endAsNumber(),
            );
        }
        // @phpstan-ignore-next-line sorter doesn't actually change the type of this array
        $sorter->sort($this->ranges);
    }
}