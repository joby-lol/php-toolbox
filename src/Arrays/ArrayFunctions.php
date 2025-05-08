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

use InvalidArgumentException;

class ArrayFunctions
{
    /**
     * Shift up to n elements from the start of an array, and return their
     * values in a new array. This is equivalent to calling array_shift n times
     * on the original array and putting the resulting values in a new array.
     * Only values are preserved, and keys are discarded.
     *
     * If the array has fewer than n elements, all of the elements will be
     * shifted and the original array will be empty.
     *
     * @template T
     * @param array<T> &$array 
     * @param int<1,max> $n 
     * @return array<T>
     */
    public static function shift_n(array &$array, int $n): array
    {
        $values = [];
        while ($array && count($values) < $n) {
            $values[] = array_shift($array);
        }
        return $values;
    }

    /**
     * Shift up to n elements from the end of an array, and return their values
     * in a new array. This is equivalent to calling array_pop n times on the
     * original array and putting the resulting values in a new array. Only
     * values are preserved, and keys are discarded.
     *
     * If the array has fewer than n elements, all of the elements will be
     * shifted and the original array will be empty.
     *
     * @template T
     * @param array<T> &$array 
     * @param int<1,max> $n 
     * @return array<T>
     */
    public static function pop_n(array &$array, int $n): array
    {
        $values = [];
        while ($array && count($values) < $n) {
            $values[] = array_pop($array);
        }
        return $values;
    }

    /**
     * Mostly this behaves like the built-in min function, but it has an
     * optional parameter to control whether null values are considered high or
     * low. By default they are considered low.
     *
     * @template T
     * @param iterable<T|null> $data 
     * @param bool $null_high 
     * @return T|null 
     */
    public static function min(iterable $data, bool $null_high = false): mixed
    {
        $values = [];
        foreach ($data as $value) {
            // if nulls are low, they are the lowest value possible so short-circuit if we find one
            if (!$null_high && is_null($value)) return null;
            $values[] = $value;
        }
        if (empty($values)) throw new InvalidArgumentException("Minimum is undefined if there are no values");
        $values = array_unique($values);
        if ($null_high) {
            usort(
                $values,
                function ($a, $b) {
                    if ($a === $b) return 0;
                    if (is_null($a)) return 1;
                    if (is_null($b)) return -1;
                    return $a <=> $b;
                }
            );
        } else {
            usort(
                $values,
                function ($a, $b) {
                    if ($a === $b) return 0;
                    if (is_null($a)) return -1;
                    if (is_null($b)) return 1;
                    return $a <=> $b;
                }
            );
        }
        return $values[0];
    }

    /**
     * Mostly this behaves like the built-in max function, but it has an
     * optional parameter to control whether null values are considered high or
     * low. By default they are considered low.
     * 
     * @template T
     * @param iterable<T|null> $data 
     * @param bool $null_high 
     * @return T|null 
     */
    public static function max(iterable $data, bool $null_high = false): mixed
    {
        $values = [];
        foreach ($data as $value) {
            // if nulls are high, they are the highest value possible so short-circuit if we find one
            if ($null_high && is_null($value)) return null;
            $values[] = $value;
        }
        if (empty($values)) throw new InvalidArgumentException("Minimum is undefined if there are no values");
        $values = array_unique($values);
        if ($null_high) {
            usort(
                $values,
                function ($a, $b) {
                    if ($a === $b) return 0;
                    if (is_null($a)) return 1;
                    if (is_null($b)) return -1;
                    return $a <=> $b;
                }
            );
        } else {
            usort(
                $values,
                function ($a, $b) {
                    if ($a === $b) return 0;
                    if (is_null($a)) return -1;
                    if (is_null($b)) return 1;
                    return $a <=> $b;
                }
            );
        }
        return end($values);
    }
}
