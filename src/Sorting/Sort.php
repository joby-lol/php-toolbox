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

/**
 * Static class for sorting arrays using sets of callbacks. This is useful for
 * cases where you want to sort an array by multiple criteria, and progressively
 * move down the list of sorting criteria as needed to break ties.
 *
 * This class is a wrapper around the Sorter class, and is useful for cases
 * where you only need to sort a single array and don't need to reuse the same
 * set of sorters on multiple arrays. If you're going to be sorting many arrays
 * using the same set of sorters it would be more efficient to instantiate a
 * Sorter object and reuse it.
 */
class Sort
{
    /**
     * Sort an array using an array of callable sorters that will be passed
     * pairs of items from the array, and the array will be sorted based on the
     * results of the first sorter that returns a non-zero value for any
     * given pair of items.
     * 
     * This method takes its array by reference and sorts it in place to reduce
     * memory use.
     * 
     * Example sorting integers:
     * 
     * ```php
     * $data = [3, 1, 4, 1, 5, 9];
     * Sort::sort($data, fn ($a, $b) => $a <=> $b);
     * ```
     * 
     * Example sorting integers with even numbers first:
     * 
     * ```php
     * $data = [3, 1, 4, 1, 5, 9];
     * Sort::sort($data, fn ($a, $b) => $a % 2 <=> $b % 2, fn ($a, $b) => $a <=> $b);
     * ```
     * 
     * @param array<mixed> &$data The array to sort, passed by reference.
     * @param callable(mixed, mixed): int ...$comparisons An array of comparison callbacks that will be used to sort the array.
     */
    public static function sort(array &$data, callable ...$comparisons): void
    {
        (new Sorter(...$comparisons))->sort($data);
    }

    /**
     * Reverse the order of a sorting callback.
     * 
     * Example sorting integers in reverse order:
     * 
     * ```php
     * $data = [3, 1, 4, 1, 5, 9];
     * Sort::sort($data, Sort::reverse(fn ($a, $b) => $a <=> $b));
     * ```
     * 
     * @param callable(mixed, mixed): int $comparison The original comparison callback.
     * @return callable(mixed, mixed): int A new comparison callback that will reverse the order of the original callback.
     */
    public static function reverse(callable $comparison): callable
    {
        return function ($a, $b) use ($comparison) {
            return $comparison($b, $a);
        };
    }

    /**
     * Create a comparison callback that will call the same method on two
     * objects and compare the results for sorting, optionally passing arguments
     * to the methods.
     * 
     * Example given a class with a method `getNumber()`:
     * 
     * ```php
     * $data = [...]; // array of objects with getNumber() method
     * Sort::sort($data, Sort::compareMethods('getNumber'));
     * ```
     * 
     * @param string $method_name The name of the method to call on the objects.
     * @param mixed ...$args Optional arguments to pass to the method.
     * @return callable(object, object): int A comparison callback that will compare the results of calling the method on two objects.
     */
    public static function compareMethods(string $method_name, mixed ...$args): callable
    {
        return function (object $a, object $b) use ($method_name, $args): int {
            assert(method_exists($a, $method_name), sprintf('Method %s does not exist on object %s', $method_name, get_class($a)));
            assert(method_exists($b, $method_name), sprintf('Method %s does not exist on object %s', $method_name, get_class($b)));
            return $a->$method_name(...$args) <=> $b->$method_name(...$args);
        };
    }

    /**
     * Create a comparison callback that will compare the values of the same
     * property on two objects for sorting.
     * 
     * Example given a class with a property `itemName`:
     * 
     * ```php
     * $data = [...]; // array of objects with itemName property
     * Sort::sort($data, Sort::compareProperties('itemName'));
     * ```
     * 
     * @param string $property_name The name of the property to compare.
     * @return callable(object, object): int A comparison callback that will compare the values of the property on two objects.
     */
    public static function compareProperties(string $property_name): callable
    {
        return function (object $a, object $b) use ($property_name): int {
            return $a->$property_name <=> $b->$property_name;
        };
    }

    /**
     * Create a comparison callback that will compare the values of the same
     * key in two arrays for sorting.
     * 
     * Example sorting by the 'name' key:
     * 
     * ```php
     * $data = [
     *    ['name' => 'apple'],
     *    ['name' => 'banana'],
     *    ['name' => 'cherry'],
     * ];
     * Sort::sort($data, Sort::compareArrayValues('name'));
     * ```
     * @param string $key The key to compare in the arrays.
     * @return callable(array<mixed>, array<mixed>): int A comparison callback that will compare the values of the key in two arrays.
     */
    public static function compareArrayValues(string $key): callable
    {
        return function (array $a, array $b) use ($key): int {
            return @$a[$key] <=> @$b[$key];
        };
    }

    /**
     * Create a comparison callback that will run a callback on items and
     * compare the results for sorting.
     * 
     * Example sorting by the length of strings:
     * 
     * ```php
     * $data = ['apple', 'banana', 'cherry', 'date', 'elderberry'];
     * Sort::sort($data, Sort::compareCallbackResults(strlen(...)));
     * ```
     * 
     * @param callable(mixed): mixed $callback The callback to run on items to compare. Its output will be sorted normally using <=>.
     */
    public static function compareCallbackResults(callable $callback): callable
    {
        return function (mixed $a, mixed $b) use ($callback): int {
            return $callback($a) <=> $callback($b);
        };
    }
}
