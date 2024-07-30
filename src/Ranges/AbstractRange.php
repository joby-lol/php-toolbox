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

use RuntimeException;

/**
 * Class to represent a range of values, which consists of a start and an end
 * value, each of which may be null to indicate an open range in that direction.
 * Any class tat extends this must implement some kind of ordering/hashing
 * mechanism to convert the values you want to express into integers, so that
 * they can be compared and determined to be "adjacent".
 *
 * For example, a range of dates could be represented by converting the date
 * into a timestamp for if you wanted a resolution of 1 second, or by converting
 * it to the number of days since some early epoch start if you wanted day
 * resolution. The details of how things are converted mostly only matter if you
 * are going to be checking for adjacency. If you don't need adjacency checks it
 * only matters that your method maintains proper ordering.
 *
 * @template T of mixed
 */
abstract class AbstractRange
{
    protected int|float $start;
    protected int|float $end;
    protected mixed $start_value;
    protected mixed $end_value;

    /**
     * This must be essentially a hash function, which converts a given value
     * into an integer, which represents its ordering somehow.
     * @param T $value 
     * @return int 
     */
    abstract protected static function valueToInteger(mixed $value): int;

    /**
     * This must be the inverse of the valueToInteger method, which converts an
     * integer back into the original value.
     * @param int $integer
     * @return T
     */
    abstract protected static function integerToValue(int $integer): mixed;

    /**
     * This must prepare a value to be stored in this object, which may just be
     * passing it blindly, cloning an object, or rounding it, etc.
     * @param T $value
     * @return T
     */
    abstract protected static function prepareValue(mixed $value): mixed;

    /**
     * This must return the value that is immediately before a given integer.
     * Returns null if number is infinite.
     * @return T|null
     */
    protected static function valueBefore(int|float $number): mixed
    {
        if ($number == INF) return null;
        if ($number == -INF) return null;
        return static::integerToValue((int)$number - 1);
    }

    /**
     * This must return the value that is immediately after a given integer.
     * Returns null if number is infinite.
     * @return T|null
     */
    protected static function valueAfter(int|float $number): mixed
    {
        if ($number == INF) return null;
        if ($number == -INF) return null;
        return static::integerToValue((int)$number + 1);
    }

    /**
     * @param T|null $start
     * @param T|null $end
     */
    final public function __construct($start, $end)
    {
        $this->setStart($start);
        $this->setEnd($end);
    }

    /**
     * Perform a boolean AND operation on this range and another, returning the
     * range that they both cover, returns null if they do not overlap.
     * @param static $other
     */
    public function booleanAnd(AbstractRange $other): static|null
    {
        if ($this->contains($other)) return new static($other->start(), $other->end());
        elseif ($other->contains($this)) return new static($this->start(), $this->end());
        elseif ($this->intersects($other)) {
            return new static(
                $this->extendsBefore($other) ? $other->start() : $this->start(),
                $this->extendsAfter($other) ? $other->end() : $this->end()
            );
        } else return null;
    }

    /**
     * Perform a boolean OR operation on this range and another, returning an
     * array of all areas that are covered by either range (if the ranges do not
     * overlap this array will contain both ranges separately). Separate objects
     * must be returned in ascending order.
     * @param static $other
     * @return RangeCollection<static>
     */
    public function booleanOr(AbstractRange $other): RangeCollection
    {
        if ($this->intersects($other) || $this->adjacent($other)) {
            return RangeCollection::create(
                new static(
                    $this->extendsBefore($other) ? $this->start() : $other->start(),
                    $this->extendsAfter($other) ? $this->end() : $other->end()
                )
            );
        } else {
            if ($this->extendsBefore($other)) {
                return RangeCollection::create(
                    new static($this->start(), $this->end()),
                    new static($other->start(), $other->end())
                );
            } else {
                return RangeCollection::create(
                    new static($other->start(), $other->end()),
                    new static($this->start(), $this->end())
                );
            }
        }
    }

    /**
     * Perform a boolean XOR operation on this range and another, returning an
     * array of all areas that are covered by either range but not both. If the
     * ranges do not overlap, this array will contain both ranges separately.
     * Separate objects must be returned in ascending order.
     * @param static $other
     * @return RangeCollection<static>
     */
    public function booleanXor(AbstractRange $other): RangeCollection
    {
        // if the ranges are equal, return an empty array
        if ($this->equals($other)) return RangeCollection::createEmpty($other);
        // if the ranges are adjacent return a single range
        if ($this->adjacent($other)) return $this->booleanOr($other);
        // if the ranges do not overlap, return both ranges
        if (!$this->intersects($other)) {
            return RangeCollection::create(new static($this->start(), $this->end()), new static($other->start(), $other->end()));
        }
        // otherwise get the maximum bounds minus wherever these intersect
        $range = new static(
            $this->extendsBefore($other) ? $this->start() : $other->start(),
            $this->extendsAfter($other) ? $this->end() : $other->end()
        );
        if ($intersect = $this->booleanAnd($other)) {
            return $range->booleanNot($intersect);
        } else {
            return RangeCollection::create($range);
        }
    }

    /**
     * Find all areas that are covered by both this range and another, sliced
     * into up to three different ranges along the boundaries other boolean
     * operations would break on. Areas will be returned in ascending order, and
     * some information about the relationships between the ranges can be inferred
     * from the number fo ranges returned here:
     * - 1 range: the entered ranges are equal
     * - 2 ranges: the entered ranges are adjacent, disjoint, or overlap with a shared boundary
     * - 3 ranges: the entered ranges overlap with space on each end
     * @param static $other
     * @return RangeCollection<static>
     */
    public function booleanSlice(AbstractRange $other): RangeCollection
    {
        // if the ranges are equal, return a single range
        if ($this->equals($other)) return RangeCollection::create(new static($this->start(), $this->end()));
        // if the ranges do not overlap, return two ranges
        if (!$this->intersects($other)) {
            return RangeCollection::create(
                new static($this->start(), $this->end()),
                new static($other->start(), $other->end())
            );
        }
        // otherwise get the maximum bounds minus wherever these intersect
        $overall_range = new static(
            $this->extendsBefore($other) ? $this->start() : $other->start(),
            $this->extendsAfter($other) ? $this->end() : $other->end()
        );
        $intersection = $this->booleanAnd($other);
        assert($intersection !== null);
        $xor = $overall_range->booleanNot($intersection);
        if (count($xor) == 2) {
            assert(isset($xor[0], $xor[1]));
            return RangeCollection::create($xor[0], $intersection, $xor[1]);
        } elseif (count($xor) == 1) {
            assert(isset($xor[0]));
            return RangeCollection::create($intersection, $xor[0]);
        }
        // throw an exception if we get in an unexpected state
        throw new RuntimeException(sprintf("Unexpected state (%s,%s) (%s,%s)", $this->start, $this->end, $other->start, $other->end));
    }

    /**
     * Perform a boolean NOT operation on this range and another, returning an
     * array of all areas that are covered by this range but not the other. If
     * the other range completely covers this range, an empty array will be
     * returned. Separate objects must be returned in ascending order.
     * @param static $other
     * @return RangeCollection<static>
     */
    public function booleanNot(AbstractRange $other): RangeCollection
    {
        // if this range is completely contained by the other, return an empty array
        if ($other->contains($this)) {
            return RangeCollection::createEmpty($other);
        }
        // if the ranges do not overlap, return this range
        if (!$this->intersects($other)) {
            return RangeCollection::create(new static($this->start(), $this->end()));
        }
        // if this range completely contains the other, return the range from the start of this range to the start of the other
        if ($this->contains($other)) {
            if ($this->start == $other->start) {
                return RangeCollection::create(new static(static::valueAfter($other->end), $this->end()));
            } elseif ($this->end == $other->end) {
                return RangeCollection::create(new static($this->start(), static::valueBefore($other->start)));
            } else {
                return RangeCollection::create(
                    new static($this->start(), static::valueBefore($other->start)),
                    new static(static::valueAfter($other->end), $this->end())
                );
            }
        }
        // if this range extends before the other, return the range from the start of this range to the start of the other
        if ($this->extendsBefore($other)) {
            return RangeCollection::create(new static($this->start(), static::valueBefore($other->start)));
        }
        // if this range extends after the other, return the range from the end of the other to the end of this range
        if ($this->extendsAfter($other)) {
            return RangeCollection::create(new static(static::valueAfter($other->end), $this->end()));
        }
        // throw an exception if we get in an unexpected state
        throw new RuntimeException(sprintf("Unexpected state (%s,%s) (%s,%s)", $this->start, $this->end, $other->start, $other->end));
    }

    /**
     * Check if this range has the same start and end as another range.
     * @param static $other
     */
    public function equals(AbstractRange $other): bool
    {
        return $this->start == $other->start && $this->end == $other->end;
    }

    /**
     * Check if any part of this range overlaps with another range.
     * @param static $other
     */
    public function intersects(AbstractRange $other): bool
    {
        if ($this->start > $other->end) return false;
        if ($this->end < $other->start) return false;
        return true;
    }

    /**
     * Check if this range completely contains another range.
     * @param static $other
     */
    public function contains(AbstractRange $other): bool
    {
        if ($this->start > $other->start) return false;
        if ($this->end < $other->end) return false;
        return true;
    }

    /**
     * Check if the end of this range is after the end of another range
     * @param static $other
     */
    public function extendsAfter(AbstractRange $other): bool
    {
        return $this->end > $other->end;
    }

    /**
     * Check if the start of this range is before the start of another range
     * @param static $other
     */
    public function extendsBefore(AbstractRange $other): bool
    {
        return $this->start < $other->start;
    }

    /**
     * Check if this range is directly adjacent to but not overlapping another range. This
     * is equivalent to checking both abutsStartOf and abutsEndOf.
     * @param static $other
     */
    public function adjacent(AbstractRange $other): bool
    {
        return $this->adjacentRightOf($other) || $this->adjacentLeftOf($other);
    }

    /**
     * Check if the start of this range directly abuts the end of another range.
     * This means that they do not overlap, but are directly adjacent.
     * @param static $other
     */
    public function adjacentRightOf(AbstractRange $other): bool
    {
        if ($this->start == -INF || $other->end == INF) return false;
        return $this->start == $other->end + 1;
    }

    /**
     * Check if the end of this range directly abuts the start of another range.
     * This means that they do not overlap, but are directly adjacent.
     * @param static $other
     */
    public function adjacentLeftOf(AbstractRange $other): bool
    {
        if ($this->end == INF || $other->start == -INF) return false;
        return $this->end == $other->start - 1;
    }

    /**
     * @param T|null $start 
     * @return static 
     */
    public function setStart(mixed $start): static
    {
        $this->start = is_null($start) ? -INF
            : static::valueToInteger($start);
        $this->start_value = is_null($start) ? null
            : static::prepareValue($start);
        return $this;
    }

    /**
     * @param T|null $end 
     * @return static 
     */
    public function setEnd(mixed $end): static
    {
        $this->end = is_null($end) ? INF
            : static::valueToInteger($end);
        $this->end_value = is_null($end) ? null
            : static::prepareValue($end);
        return $this;
    }

    /**
     * @return T|null
     */
    public function start(): mixed
    {
        return $this->start_value;
    }

    /**
     * @return T|null
     */
    public function end(): mixed
    {
        return $this->end_value;
    }

    public function startAsNumber(): int|float
    {
        return $this->start;
    }

    public function endAsNumber(): int|float
    {
        return $this->end;
    }
}
