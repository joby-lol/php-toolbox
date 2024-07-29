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
     * @param T|null $value 
     * @return int 
     */
    abstract protected static function convertToInt(mixed $value): int;

    /**
     * This must prepare a value to be stored in this object, which may just be
     * passing it blindly, cloning an object, or rounding it, etc.
     * @param T $value
     * @return T
     */
    abstract protected static function prepareValue(mixed $value): mixed;

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
    public function abuts(AbstractRange $other): bool
    {
        return $this->abutsEndOf($other) || $this->abutsStartOf($other);
    }

    /**
     * Check if the start of this range directly abuts the end of another range.
     * This means that they do not overlap, but are directly adjacent.
     * @param static $other
     */
    public function abutsEndOf(AbstractRange $other): bool
    {
        if ($this->start == -INF || $other->end == INF) return false;
        return $this->start == $other->end + 1;
    }

    /**
     * Check if the end of this range directly abuts the start of another range.
     * This means that they do not overlap, but are directly adjacent.
     * @param static $other
     */
    public function abutsStartOf(AbstractRange $other): bool
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
            : static::convertToInt($start);
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
            : static::convertToInt($end);
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
}
