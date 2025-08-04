# Joby's PHP Toolbox

[![CI](https://github.com/joby-lol/php-toolbox/actions/workflows/ci.yml/badge.svg)](https://github.com/joby-lol/php-toolbox/actions/workflows/ci.yml)

A lightweight collection of useful general purpose PHP tools with no dependencies. Committed to always at least having minimal dependencies.

## Array Tools

The `ArrayFunctions` class provides enhanced array manipulation capabilities:

- **shift_n**: Shifts multiple elements from the start of an array at once
  ```php
  $array = [1, 2, 3, 4, 5];
  $shifted = ArrayFunctions::shift_n($array, 2); // Returns [1, 2]
  // $array is now [3, 4, 5]
  ```

- **pop_n**: Pops multiple elements from the end of an array at once
  ```php
  $array = [1, 2, 3, 4, 5];
  $popped = ArrayFunctions::pop_n($array, 2); // Returns [5, 4]
  // $array is now [1, 2, 3]
  ```

- **min/max**: Enhanced versions of PHP's min/max functions with special handling for null values
  ```php
  $array = [1, 2, 3, null, 5];
  $min = ArrayFunctions::min($array); // Returns null (nulls are low by default)
  $min = ArrayFunctions::min($array, true); // Returns 1 (nulls are high)
  $max = ArrayFunctions::max($array); // Returns 5 (nulls are low by default)
  $max = ArrayFunctions::max($array, true); // Returns null (nulls are high)
  ```

## Sorting Tools

The sorting component provides flexible and powerful sorting capabilities:

### Sort Class

Static interface for one-time sorting operations:

```php
// Basic sorting
$data = [3, 1, 4, 1, 5, 9];
Sort::sort($data, fn ($a, $b) => $a <=> $b);

// Multi-criteria sorting (even numbers first, then by value)
$data = [3, 1, 4, 1, 5, 9];
Sort::sort($data, fn ($a, $b) => $a % 2 <=> $b % 2, fn ($a, $b) => $a <=> $b);

// Reverse sorting
Sort::sort($data, Sort::reverse(fn ($a, $b) => $a <=> $b));

// Sorting objects by method results
Sort::sort($objects, Sort::compareMethods('getNumber'));

// Sorting objects by property values
Sort::sort($objects, Sort::compareProperties('itemName'));

// Sorting arrays by key values
Sort::sort($arrayOfArrays, Sort::compareArrayValues('name'));

// Sorting by callback results (e.g., string length)
Sort::sort($strings, Sort::compareCallbackResults(strlen(...)));
```

### Sorter Class

For reusable sorting operations:

```php
// Create a sorter with multiple criteria
$sorter = new Sorter(
    fn ($a, $b) => $a->priority <=> $b->priority,
    fn ($a, $b) => $a->name <=> $b->name
);

// Sort multiple arrays with the same criteria
$sorter->sort($array1);
$sorter->sort($array2);
```

### Sortable Interface

Objects can implement the `Sortable` interface to provide a default sorting value:

```php
class MyClass implements Sortable {
    public function sortByValue(): string|int|float|bool {
        return $this->priority;
    }
}
```

## Range Tools

The Ranges component provides tools for working with ranges of values:

### AbstractRange

An abstract base class for implementing ranges with various value types:

- Boolean operations: `booleanAnd`, `booleanOr`, `booleanXor`, `booleanNot`
- Comparison methods: `equals`, `intersects`, `contains`, `adjacent`
- Boundary management: `setStart`, `setEnd`, `start`, `end`

```php
// Example using IntegerRange implementation
$range1 = new IntegerRange(1, 5);
$range2 = new IntegerRange(3, 8);

// Intersection (AND)
$intersection = $range1->booleanAnd($range2); // Range from 3 to 5

// Union (OR)
$union = $range1->booleanOr($range2); // Collection with range from 1 to 8

// Exclusive OR
$xor = $range1->booleanXor($range2); // Collection with ranges 1-3 and 5-8
```

### RangeCollection

Manages collections of ranges with operations for merging and manipulation:

```php
// Create a collection of ranges
$collection = RangeCollection::create($range1, $range2);

// Merge overlapping and adjacent ranges
$merged = $collection->mergeRanges();
```

### IntegerRange

A concrete implementation of AbstractRange for integer values.

