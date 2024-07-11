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

use PHPUnit\Framework\TestCase;

/**
 * Test cases for the static Sort class, including its helpers for creating
 * useful comparison callbacks.
 */
class SortTest extends TestCase
{
    /**
     * This is just ensuring that the static class is set up reasonably okay,
     * because Sorter is tested elsewhere and this just uses that.
     */
    function testSort()
    {
        $data = [3, 1, 4, 1, 5, 9];
        Sort::sort($data, fn ($a, $b) => $a <=> $b);
        $this->assertEquals([1, 1, 3, 4, 5, 9], $data);
    }

    /**
     * Test the reverse method to ensure it flips the order of a comparison.
     */
    function testReverse()
    {
        $data = [3, 1, 4, 1, 5, 9];
        Sort::sort($data, Sort::reverse(fn ($a, $b) => $a <=> $b));
        $this->assertEquals([9, 5, 4, 3, 1, 1], $data);
    }

    /**
     * Test the compareProperties method to ensure it creates a comparison callback
     * that calls the same method on two objects and compares the results.
     */
    function testCompareProperties()
    {
        $data = [
            (object) ['name' => 'apple'],
            (object) ['name' => 'banana'],
            (object) ['name' => 'cherry'],
            (object) ['name' => 'date'],
            (object) ['name' => 'elderberry'],
        ];
        Sort::sort($data, Sort::compareProperties('name'));
        $this->assertEquals([
            (object) ['name' => 'apple'],
            (object) ['name' => 'banana'],
            (object) ['name' => 'cherry'],
            (object) ['name' => 'date'],
            (object) ['name' => 'elderberry'],
        ], $data);
    }

    function testCompareMethods()
    {
        $data = [
            new SortTestComparePropertiesHarness(9),
            new SortTestComparePropertiesHarness(117),
            new SortTestComparePropertiesHarness(28),
            new SortTestComparePropertiesHarness(6),
            new SortTestComparePropertiesHarness(212),
            new SortTestComparePropertiesHarness(323),
        ];
        // default value of method is mod 10, so it should sort by the last digit
        Sort::sort($data, Sort::compareMethods('value'));
        $this->assertEquals([
            new SortTestComparePropertiesHarness(212),
            new SortTestComparePropertiesHarness(323),
            new SortTestComparePropertiesHarness(6),
            new SortTestComparePropertiesHarness(117),
            new SortTestComparePropertiesHarness(28),
            new SortTestComparePropertiesHarness(9),
        ], $data);
        // now sort passing an argument, mod 100 so it should sort by the last 2 digits
        Sort::sort($data, Sort::compareMethods('value', 100));
        $this->assertEquals([
            new SortTestComparePropertiesHarness(6),
            new SortTestComparePropertiesHarness(9),
            new SortTestComparePropertiesHarness(212),
            new SortTestComparePropertiesHarness(117),
            new SortTestComparePropertiesHarness(323),
            new SortTestComparePropertiesHarness(28),

        ], $data);
    }

    /**
     * Test that the callbacks created by compareArrayValues() succesfully sort
     * arrays by a particular value within them.
     */
    function testCompareArrayValues()
    {
        $data = [
            ['name' => 'apple'],
            ['foo' => 'bar', 'name' => 'banana'],
            ['name' => 'cherry'],
            ['name' => 'date', 'buzz' => 'baz'],
            ['name' => 'elderberry'],
        ];
        Sort::sort($data, Sort::compareArrayValues('name'));
        $this->assertEquals([
            ['name' => 'apple'],
            ['foo' => 'bar', 'name' => 'banana'],
            ['name' => 'cherry'],
            ['name' => 'date', 'buzz' => 'baz'],
            ['name' => 'elderberry'],
        ], $data);
    }

    /**
     * Test that the compareCallbackResults() method works as expected by
     * sorting a list of integers by their value mod 10.
     */
    function testCompareCallbackResults()
    {
        $data = [9, 17, 28, 6, 12, 23];
        Sort::sort($data, Sort::compareCallbackResults(fn ($a) => $a % 10));
        $this->assertEquals([12, 23, 6, 17, 28, 9], $data);
    }
}

/**
 * Harness object for testing the compareProperties method.
 */
class SortTestComparePropertiesHarness
{
    public int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function value(int $mod = 10): int
    {
        return $this->value % $mod;
    }
}
