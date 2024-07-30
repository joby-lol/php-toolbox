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

namespace Joby\Toolbox\Sorting;

use Joby\Toolbox\Ranges\IntegerRange;
use Joby\Toolbox\Ranges\RangeCollection;
use PHPUnit\Framework\TestCase;

class RangeCollectionTest extends TestCase
{

    public function testEmpty()
    {
        $collection = RangeCollection::createEmpty(IntegerRange::class);
        $this->assertEquals(
            '',
            (string)$collection
        );
        $this->assertEquals(
            0,
            count($collection)
        );
        $this->assertEquals(
            [],
            $collection->toArray()
        );
    }

    public function testSorting()
    {
        // basic in-order sorting, sorted by start date
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4)
        );
        $this->assertEquals(
            '[1...2], [2...4], [3...4]',
            (string)$collection
        );
        // ties in the start date are broken by which has an earlier end date
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3)
        );
        $this->assertEquals(
            '[1...2], [2...3], [2...4], [3...4]',
            (string)$collection
        );
        // infinite start dates go first
        $collection = RangeCollection::create(
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3),
            new IntegerRange(null, 2)
        );
        $this->assertEquals(
            '[...2], [2...3], [2...4], [3...4]',
            (string)$collection
        );
        // infinite end dates go last
        $collection = RangeCollection::create(
            new IntegerRange(2, 4),
            new IntegerRange(2, null),
            new IntegerRange(2, 3),
        );
        $this->assertEquals(
            '[2...3], [2...4], [2...]',
            (string)$collection
        );
    }

    public function testFilter()
    {
        // filter a collection to only include ranges that start with 2
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3)
        );
        $filtered = $collection->filter(function ($range) {
            return $range->start() == 2;
        });
        $this->assertEquals(
            '[2...3], [2...4]',
            (string)$filtered
        );
    }

    public function testMap()
    {
        // map a collection to include only the end date of each range
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3)
        );
        $mapped = $collection->map(function ($range) {
            return new IntegerRange(null, $range->end());
        });
        $this->assertEquals(
            '[...2], [...3], [...4], [...4]',
            (string)$mapped
        );
        // map a collection to make a one-unit collection of the start and end of each range
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3)
        );
        $mapped = $collection->map(function ($range) {
            return RangeCollection::create(
                new IntegerRange($range->start(), $range->start()),
                new IntegerRange($range->end(), $range->end())
            );
        });
        $this->assertEquals(
            '[1...1], [2...2], [2...2], [2...2], [3...3], [3...3], [4...4], [4...4]',
            (string)$mapped
        );
        // map a collection to remove any range that starts with 2
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3)
        );
        $mapped = $collection->map(function ($range) {
            if ($range->start() == 2) return null;
            return $range;
        });
        $this->assertEquals(
            '[1...2], [3...4]',
            (string)$mapped
        );
    }

    public function testMergeIntersectingRanges()
    {
        // simple intersecting ranges
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $merged = $collection->mergeIntersectingRanges();
        $this->assertEquals(
            '[1...4]',
            (string)$merged
        );
        // two groups of intersecting ranges that are adjacent but not overlapping
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3),
            new IntegerRange(5, 6),
            new IntegerRange(7, 8),
            new IntegerRange(6, 8),
            new IntegerRange(6, 7)
        );
        $merged = $collection->mergeIntersectingRanges();
        $this->assertEquals(
            '[1...4], [5...8]',
            (string)$merged
        );
    }

    public function testMergeRanges()
    {
        // this method does everything mergeIntersectingRanges does, plus it
        // merges adjacent ranges effectively this method turns a collection of
        // ranges into the smallest possible set of ranges that fully contain
        // all the original ranges
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $merged = $collection->mergeRanges();
        $this->assertEquals(
            '[1...4]',
            (string)$merged
        );
        // two groups of intersecting ranges that are adjacent but not overlapping
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3),
            new IntegerRange(5, 6),
            new IntegerRange(7, 8),
            new IntegerRange(6, 8),
            new IntegerRange(6, 7)
        );
        $merged = $collection->mergeRanges();
        $this->assertEquals(
            '[1...8]',
            (string)$merged
        );
        // two groups that are not adjacent
        $collection = RangeCollection::create(
            new IntegerRange(1, 2),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
            new IntegerRange(2, 3),
            new IntegerRange(5, 6),
            new IntegerRange(7, 8),
            new IntegerRange(6, 8),
            new IntegerRange(6, 7),
            new IntegerRange(10, 11),
            new IntegerRange(12, 13),
            new IntegerRange(11, 13),
            new IntegerRange(11, 12)
        );
        $merged = $collection->mergeRanges();
        $this->assertEquals(
            '[1...8], [10...13]',
            (string)$merged
        );
    }

    public function testBooleanNot()
    {
        // subtract a range from a collection
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $subtracted = $collection->booleanNot(new IntegerRange(2, 3));
        $this->assertEquals(
            '[1...1], [4...4], [4...4]',
            (string)$subtracted
        );
        // subtract a range from a collection that is fully contained
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $subtracted = $collection->booleanNot(new IntegerRange(2, 4));
        $this->assertEquals(
            '[1...1]',
            (string)$subtracted
        );
        // subtract a range from a collection that fully contains the range
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $subtracted = $collection->booleanNot(new IntegerRange(1, 4));
        $this->assertEquals(
            '',
            (string)$subtracted
        );
        // subtract a range from a collection that is fully contained
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $subtracted = $collection->booleanNot(new IntegerRange(1, 3));
        $this->assertEquals(
            '[4...4], [4...4]',
            (string)$subtracted
        );
    }

    public function testBooleanAnd()
    {
        // intersect a range with a collection
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $intersected = $collection->booleanAnd(new IntegerRange(2, 3));
        $this->assertEquals(
            '[2...3], [2...3], [3...3]',
            (string)$intersected
        );
        // intersect a range with a collection that is fully contained
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $intersected = $collection->booleanAnd(new IntegerRange(2, 4));
        $this->assertEquals(
            '[2...3], [2...4], [3...4]',
            (string)$intersected
        );
        // intersect a range with a collection that fully contains the range
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $intersected = $collection->booleanAnd(new IntegerRange(1, 4));
        $this->assertEquals(
            '[1...3], [2...4], [3...4]',
            (string)$intersected
        );
        // intersect a range with a collection that is fully contained
        $collection = RangeCollection::create(
            new IntegerRange(1, 3),
            new IntegerRange(3, 4),
            new IntegerRange(2, 4),
        );
        $intersected = $collection->booleanAnd(new IntegerRange(1, 3));
        $this->assertEquals(
            '[1...3], [2...3], [3...3]',
            (string)$intersected
        );
    }
}
