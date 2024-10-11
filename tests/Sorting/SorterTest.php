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

namespace Joby\Toolbox\Sorting;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for the Sorter class.
 */
class SorterTest extends TestCase
{
    /**
     * Confirm that sorting an empty array works.
     */
    public function testEmptyArray()
    {
        $data = [];
        $sorter = new Sorter();
        $sorter->sort($data);
        $this->assertEquals([], $data);
    }

    /**
     * Confirm that sorting an array of integers works.
     */
    public function testSortingIntegers()
    {
        $data = [3, 1, 4, 1, 5, 9];
        $sorter = new Sorter(fn ($a, $b) => $a <=> $b);
        $sorter->sort($data);
        $this->assertEquals([1, 1, 3, 4, 5, 9], $data);
    }

    /**
     * Confirm that sorting an array of strings works.
     */
    public function testSortingStrings()
    {
        $data = ['apple', 'banana', 'cherry', 'date', 'elderberry'];
        $sorter = new Sorter(fn ($a, $b) => strcmp($a, $b));
        $sorter->sort($data);
        $this->assertEquals(['apple', 'banana', 'cherry', 'date', 'elderberry'], $data);
    }

    /**
     * Confirm that sorting an array of strings by length works.
     */
    public function testSortingByLength()
    {
        $data = ['apple', 'banana', 'cherry', 'date', 'elderberry'];
        $sorter = new Sorter(fn ($a, $b) => strlen($a) <=> strlen($b));
        $sorter->sort($data);
        $this->assertEquals(['date', 'apple', 'banana', 'cherry', 'elderberry'], $data);
    }

    /**
     * Confirm that sorting an array of strings by length works with a second comparison
     * using strcmp to sort alphabetically in the case of a tie.
     */
    public function testSortingByLengthWithTieBreaker()
    {
        $data = ['cc', 'c', 'ccc', 'aaa', 'aa', 'a', 'b', 'bb', 'bbb'];
        $sorter = new Sorter(
            fn ($a, $b) => strlen($a) <=> strlen($b),
            fn ($a, $b) => strcmp($a, $b)
        );
        $sorter->sort($data);
        $this->assertEquals(['a', 'b', 'c', 'aa', 'bb', 'cc', 'aaa', 'bbb', 'ccc'], $data);
    }

    /**
     * Confirm that adding sorters using addSorter() works as expected.
     */
    public function testAddingSorters()
    {
        $data = ['cc', 'c', 'ccc', 'aaa', 'aa', 'a', 'b', 'bb', 'bbb'];
        $sorter = new Sorter();
        $sorter->addComparison(
            fn ($a, $b) => strlen($a) <=> strlen($b),
            fn ($a, $b) => strcmp($a, $b)
        );
        $sorter->sort($data);
        $this->assertEquals(['a', 'b', 'c', 'aa', 'bb', 'cc', 'aaa', 'bbb', 'ccc'], $data);
    }
}
