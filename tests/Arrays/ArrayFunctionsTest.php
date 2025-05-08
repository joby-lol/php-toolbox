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

namespace Joby\Toolbox\Arrays;

use PHPUnit\Framework\TestCase;

class ArrayFunctionsTest extends TestCase
{
    public function testShift_n()
    {
        // Test shifting multiple elements all the way until it's empty
        $array = [1, 2, 3, 4, 5];
        $this->assertEquals([1, 2], ArrayFunctions::shift_n($array, 2));
        $this->assertEquals([3, 4, 5], $array);
        $this->assertEquals([3, 4], ArrayFunctions::shift_n($array, 2));
        $this->assertEquals([5], $array);
        $this->assertEquals([5], ArrayFunctions::shift_n($array, 2));
        $this->assertEquals([], $array);
    }

    public function testPop_n()
    {
        // Test popping multiple elements all the way until it's empty
        $array = [1, 2, 3, 4, 5];
        $this->assertEquals([5, 4], ArrayFunctions::pop_n($array, 2));
        $this->assertEquals([1, 2, 3], $array);
        $this->assertEquals([3, 2], ArrayFunctions::pop_n($array, 2));
        $this->assertEquals([1], $array);
        $this->assertEquals([1], ArrayFunctions::pop_n($array, 2));
        $this->assertEquals([], $array);
    }

    public function testMin()
    {
        $array = [1, 2, 3, 4, 5];
        $this->assertEquals(1, ArrayFunctions::min($array));
        $array = [5, 4, 3, 2, 1];
        $this->assertEquals(1, ArrayFunctions::min($array));
        // by default nulls are low, and should be returned as if they are the lowest value
        $array = [1, 2, 3, 4, 5, null];
        $this->assertEquals(null, ArrayFunctions::min($array));
        // if nulls are high, they should be skipped because they aren't the min
        $this->assertEquals(1, ArrayFunctions::min($array, true));
        // if the array only contains a null value it should be returned either way
        $array = [null];
        $this->assertEquals(null, ArrayFunctions::min($array));
        $this->assertEquals(null, ArrayFunctions::min($array, true));
        // should behave alphabetically for strings
        $array = ['a', 'b', 'c', 'd', 'e'];
        $this->assertEquals('a', ArrayFunctions::min($array));
        $array = ['e', 'd', 'c', 'b', 'a'];
        $this->assertEquals('a', ArrayFunctions::min($array));
    }

    public function testMax()
    {
        $array = [1, 2, 3, 4, 5];
        $this->assertEquals(5, ArrayFunctions::max($array));
        $array = [5, 4, 3, 2, 1];
        $this->assertEquals(5, ArrayFunctions::max($array));
        // by default nulls are low, and should be treated as if they are the lowest value
        $array = [1, 2, 3, 4, 5, null];
        $this->assertEquals(5, ArrayFunctions::max($array));
        // if nulls are high, they should be returned as if they are the highest value
        $this->assertEquals(null, ArrayFunctions::max($array, true));
        // if the array only contains a null value it should be returned either way
        $array = [null];
        $this->assertEquals(null, ArrayFunctions::max($array));
        $this->assertEquals(null, ArrayFunctions::max($array, true));
        // should behave alphabetically for strings
        $array = ['a', 'b', 'c', 'd', 'e'];
        $this->assertEquals('e', ArrayFunctions::max($array));
        $array = ['e', 'd', 'c', 'b', 'a'];
        $this->assertEquals('e', ArrayFunctions::max($array));
    }
}
