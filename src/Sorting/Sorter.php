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
 * Class for sorting arrays using sets of callbacks. This is useful for cases
 * where you want to sort an array by multiple criteria, and progressively move
 * down the list of sorting criteria as needed to break ties. Instantiating a
 * Sorter object can be useful if you want to apply the same set of sorters to
 * multiple input arrays.
 *
 * If you're only sorting one array, it may be more convenient to use the static
 * form by calling Sort::sort() instead.
 */
class Sorter
{
    /** @var array<callable(mixed $a, mixed $b): int> $comparisons */
    protected array $comparisons = [];

    /**
     * Create a new Sorter object with the given list of sorting callbacks.
     * The sorters will be called in order, and the array will be sorted based
     * on the first one to return a non-zero value.
     *
     * @param callable ...$comparisons
     */
    public function __construct(callable ...$comparisons)
    {
        $this->comparisons = $comparisons;
    }

    /**
     * Add one or more sorting callbacks to this sorter. The new callbacks will
     * be appended to the end of the existing list of sorters.
     *
     * @param callable ...$comparisons
     */
    public function addComparison(callable ...$comparisons): static
    {
        foreach ($comparisons as $sorter) {
            $this->comparisons[] = $sorter;
        }
        return $this;
    }

    /**
     * Sort an array using the current list of callbacks. This method takes its
     * array by reference and sorts it in place to reduce memory use.
     *
     * @template TItem of mixed
     * @param array<TKey,TItem> &$data The array to sort, passed by reference.
     * @phpstan-assert array<int,TItem> $data
     */
    public function sort(array &$data): static
    {
        usort($data, static::sortItems(...));
        return $this;
    }

    /**
     * Determine which of two items should go first, or if they are a tie.
     */
    protected function sortItems(mixed $a, mixed $b): int
    {
        // sort using explicit comparisons
        foreach ($this->comparisons as $comparison) {
            $result = $comparison($a, $b);
            if ($result !== 0) {
                return $result;
            }
        }
        // attempt to sort using the built-in Sortable interface
        if ($a instanceof Sortable && $b instanceof Sortable) {
            return $a->sortByValue() <=> $b->sortByValue();
        }
        // return a tie otherwise
        return 0;
    }
}