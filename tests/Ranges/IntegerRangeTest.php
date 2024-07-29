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

use Exception;
use Joby\Toolbox\Ranges\IntegerRange;
use PHPUnit\Framework\TestCase;

class IntegerRangeTest extends TestCase
{

    public function testInstantiation()
    {
        // test fully bounded
        $range = new IntegerRange(1, 10);
        $this->assertInstanceOf(IntegerRange::class, $range);
        $this->assertEquals(1, $range->start());
        $this->assertEquals(10, $range->end());
        // test open start
        $range = new IntegerRange(null, 10);
        $this->assertInstanceOf(IntegerRange::class, $range);
        $this->assertNull($range->start());
        $this->assertEquals(10, $range->end());
        // test open end
        $range = new IntegerRange(1, null);
        $this->assertInstanceOf(IntegerRange::class, $range);
        $this->assertEquals(1, $range->start());
        $this->assertNull($range->end());
        // test open both
        $range = new IntegerRange(null, null);
        $this->assertInstanceOf(IntegerRange::class, $range);
        $this->assertNull($range->start());
        $this->assertNull($range->end());
    }

    public function testEquals()
    {
        // fully bounded
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => false,
                'intersecting open end left' => false,
                'intersecting open end right' => false,
                'intersecting open end center' => false,
                'intersecting open start left' => false,
                'intersecting open start right' => false,
                'intersecting open start center' => false,
                'disjoint open start' => false,
                'disjoint open end' => false,
                'disjoint left' => false,
                'disjoint right' => false,
                'adjacent open start' => false,
                'adjacent open end' => false,
                'adjacent left' => false,
                'adjacent right' => false,
                'contained' => false,
                'contained same start' => false,
                'contained same end' => false,
                'containing' => false,
                'containing unbounded start' => false,
                'containing unbounded end' => false,
                'containing same start' => false,
                'containing same end' => false,
                'containing same start unbounded end' => false,
                'containing same end unbounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, 20),
                'equals',
            )
        );
        // open start
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => false,
                'intersecting open start left' => false,
                'intersecting open start right' => false,
                'intersecting bounded start left' => false,
                'intersecting bounded start right' => false,
                'intersecting bounded start center' => false,
                'intersecting open end same' => false,
                'intersecting open end left' => false,
                'intersecting bounded end same' => false,
                'intersecting bounded end left' => false,
                'adjacent open end' => false,
                'adjacent bounded end' => false,
                'disjoint open end' => false,
                'disjoint bounded end' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'equals',
            )
        );
        // open end
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => false,
                'intersecting open end left' => false,
                'intersecting open end right' => false,
                'intersecting bounded end left' => false,
                'intersecting bounded end right' => false,
                'intersecting bounded end center' => false,
                'intersecting open start same' => false,
                'intersecting open start right' => false,
                'intersecting bounded start same' => false,
                'intersecting bounded start right' => false,
                'adjacent open start' => false,
                'adjacent bounded start' => false,
                'disjoint open start' => false,
                'disjoint bounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'equals',
            )
        );
        // fully unbounded
        $this->assertEquals(
            [
                'same' => true,
                'open start' => false,
                'open end' => false,
                'bounded' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'equals',
            )
        );
    }

    public function testIntersects()
    {
        // fully bounded
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => true,
                'intersecting open end left' => true,
                'intersecting open end right' => true,
                'intersecting open end center' => true,
                'intersecting open start left' => true,
                'intersecting open start right' => true,
                'intersecting open start center' => true,
                'disjoint open start' => false,
                'disjoint open end' => false,
                'disjoint left' => false,
                'disjoint right' => false,
                'adjacent open start' => false,
                'adjacent open end' => false,
                'adjacent left' => false,
                'adjacent right' => false,
                'contained' => true,
                'contained same start' => true,
                'contained same end' => true,
                'containing' => true,
                'containing unbounded start' => true,
                'containing unbounded end' => true,
                'containing same start' => true,
                'containing same end' => true,
                'containing same start unbounded end' => true,
                'containing same end unbounded start' => true,
            ],
            $this->scenarioResults(
                new IntegerRange(10, 20),
                'intersects',
            )
        );
        // open start
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => true,
                'intersecting open start left' => true,
                'intersecting open start right' => true,
                'intersecting bounded start left' => true,
                'intersecting bounded start right' => true,
                'intersecting bounded start center' => true,
                'intersecting open end same' => true,
                'intersecting open end left' => true,
                'intersecting bounded end same' => true,
                'intersecting bounded end left' => true,
                'adjacent open end' => false,
                'adjacent bounded end' => false,
                'disjoint open end' => false,
                'disjoint bounded end' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'intersects',
            )
        );
        // open end
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => true,
                'intersecting open end left' => true,
                'intersecting open end right' => true,
                'intersecting bounded end left' => true,
                'intersecting bounded end right' => true,
                'intersecting bounded end center' => true,
                'intersecting open start same' => true,
                'intersecting open start right' => true,
                'intersecting bounded start same' => true,
                'intersecting bounded start right' => true,
                'adjacent open start' => false,
                'adjacent bounded start' => false,
                'disjoint open start' => false,
                'disjoint bounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'intersects',
            )
        );
        // fully unbounded
        $this->assertEquals(
            [
                'same' => true,
                'open start' => true,
                'open end' => true,
                'bounded' => true,
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'intersects',
            )
        );
    }

    public function testContains()
    {
        // fully bounded
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => false,
                'intersecting open end left' => false,
                'intersecting open end right' => false,
                'intersecting open end center' => false,
                'intersecting open start left' => false,
                'intersecting open start right' => false,
                'intersecting open start center' => false,
                'disjoint open start' => false,
                'disjoint open end' => false,
                'disjoint left' => false,
                'disjoint right' => false,
                'adjacent open start' => false,
                'adjacent open end' => false,
                'adjacent left' => false,
                'adjacent right' => false,
                'contained' => true,
                'contained same start' => true,
                'contained same end' => true,
                'containing' => false,
                'containing unbounded start' => false,
                'containing unbounded end' => false,
                'containing same start' => false,
                'containing same end' => false,
                'containing same start unbounded end' => false,
                'containing same end unbounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, 20),
                'contains',
            )
        );
        // open start
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => false,
                'intersecting open start left' => true,
                'intersecting open start right' => false,
                'intersecting bounded start left' => true,
                'intersecting bounded start right' => false,
                'intersecting bounded start center' => true,
                'intersecting open end same' => false,
                'intersecting open end left' => false,
                'intersecting bounded end same' => false,
                'intersecting bounded end left' => false,
                'adjacent open end' => false,
                'adjacent bounded end' => false,
                'disjoint open end' => false,
                'disjoint bounded end' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'contains',
            )
        );
        // open end
        $this->assertEquals(
            [
                'same' => true,
                'unbounded' => false,
                'intersecting open end left' => false,
                'intersecting open end right' => true,
                'intersecting bounded end left' => false,
                'intersecting bounded end right' => true,
                'intersecting bounded end center' => true,
                'intersecting open start same' => false,
                'intersecting open start right' => false,
                'intersecting bounded start same' => false,
                'intersecting bounded start right' => false,
                'adjacent open start' => false,
                'adjacent bounded start' => false,
                'disjoint open start' => false,
                'disjoint bounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'contains',
            )
        );
        // fully unbounded
        $this->assertEquals(
            [
                'same' => true,
                'open start' => true,
                'open end' => true,
                'bounded' => true,
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'contains',
            )
        );
    }

    // TODO: testAbutsEndOf

    // TODO: see about making failures go to the right line/test

    public function testAbutsStartOf()
    {
        // fully bounded
        $this->assertEquals(
            [
                'same' => false,
                'unbounded' => false,
                'intersecting open end left' => false,
                'intersecting open end right' => false,
                'intersecting open end center' => false,
                'intersecting open start left' => false,
                'intersecting open start right' => false,
                'intersecting open start center' => false,
                'disjoint open start' => false,
                'disjoint open end' => false,
                'disjoint left' => false,
                'disjoint right' => false,
                'adjacent open start' => false,
                'adjacent open end' => true,
                'adjacent left' => false,
                'adjacent right' => true,
                'contained' => false,
                'contained same start' => false,
                'contained same end' => false,
                'containing' => false,
                'containing unbounded start' => false,
                'containing unbounded end' => false,
                'containing same start' => false,
                'containing same end' => false,
                'containing same start unbounded end' => false,
                'containing same end unbounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, 20),
                'abutsStartOf',
            )
        );
        // open start
        $this->assertEquals(
            [
                'same' => false,
                'unbounded' => false,
                'intersecting open start left' => false,
                'intersecting open start right' => false,
                'intersecting bounded start left' => false,
                'intersecting bounded start right' => false,
                'intersecting bounded start center' => false,
                'intersecting open end same' => false,
                'intersecting open end left' => false,
                'intersecting bounded end same' => false,
                'intersecting bounded end left' => false,
                'adjacent open end' => true,
                'adjacent bounded end' => true,
                'disjoint open end' => false,
                'disjoint bounded end' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'abutsStartOf',
            )
        );
        // open end
        $this->assertEquals(
            [
                'same' => false,
                'unbounded' => false,
                'intersecting open end left' => false,
                'intersecting open end right' => false,
                'intersecting bounded end left' => false,
                'intersecting bounded end right' => false,
                'intersecting bounded end center' => false,
                'intersecting open start same' => false,
                'intersecting open start right' => false,
                'intersecting bounded start same' => false,
                'intersecting bounded start right' => false,
                'adjacent open start' => false,
                'adjacent bounded start' => false,
                'disjoint open start' => false,
                'disjoint bounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'abutsStartOf',
            )
        );
        // fully unbounded
        $this->assertEquals(
            [
                'same' => false,
                'open start' => false,
                'open end' => false,
                'bounded' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'abutsStartOf',
            )
        );
    }

    protected function createScenarios(IntegerRange $range): array
    {
        if (is_null($range->start()) && is_null($range->end())) {
            // scenarios for fully open range
            return [
                'same' => new IntegerRange(null, null),
                'open start' => new IntegerRange(null, 10),
                'open end' => new IntegerRange(10, null),
                'bounded' => new IntegerRange(10, 20),
            ];
        } elseif (is_null($range->start())) {
            // scenarios for unbounded start
            return [
                'same' => new IntegerRange(null, $range->end()),
                'unbounded' => new IntegerRange(null, null),
                'intersecting open start left' => new IntegerRange(null, $range->end() - 2),
                'intersecting open start right' => new IntegerRange(null, $range->end() + 2),
                'intersecting bounded start left' => new IntegerRange($range->end() - 12, $range->end() - 2),
                'intersecting bounded start right' => new IntegerRange($range->end() - 8, $range->end() + 2),
                'intersecting bounded start center' => new IntegerRange($range->end() - 10, $range->end()),
                'intersecting open end same' => new IntegerRange($range->end(), null),
                'intersecting open end left' => new IntegerRange($range->end() - 2, null),
                'intersecting bounded end same' => new IntegerRange($range->end(), $range->end() + 10),
                'intersecting bounded end left' => new IntegerRange($range->end() - 2, $range->end() + 10),
                'adjacent open end' => new IntegerRange($range->end() + 1, null),
                'adjacent bounded end' => new IntegerRange($range->end() + 1, $range->end() + 10),
                'disjoint open end' => new IntegerRange($range->end() + 2, null),
                'disjoint bounded end' => new IntegerRange($range->end() + 2, $range->end() + 12),
            ];
        } elseif (is_null($range->end())) {
            // scenarios for unbounded end
            return [
                'same' => new IntegerRange($range->start(), null),
                'unbounded' => new IntegerRange(null, null),
                'intersecting open end left' => new IntegerRange($range->start() - 2, null),
                'intersecting open end right' => new IntegerRange($range->start() + 2, null),
                'intersecting bounded end left' => new IntegerRange($range->start() - 2, $range->start() + 8),
                'intersecting bounded end right' => new IntegerRange($range->start() + 2, $range->start() + 12),
                'intersecting bounded end center' => new IntegerRange($range->start(), $range->start() + 10),
                'intersecting open start same' => new IntegerRange(null, $range->start()),
                'intersecting open start right' => new IntegerRange(null, $range->start() + 2),
                'intersecting bounded start same' => new IntegerRange($range->start() - 10, $range->start()),
                'intersecting bounded start right' => new IntegerRange($range->start() - 12, $range->start() + 2),
                'adjacent open start' => new IntegerRange(null, $range->start() - 1),
                'adjacent bounded start' => new IntegerRange($range->start() - 10, $range->start() - 1),
                'disjoint open start' => new IntegerRange(null, $range->start() - 2),
                'disjoint bounded start' => new IntegerRange($range->start() - 12, $range->start() - 2),
            ];
        } else {
            // scenarios for fully bounded range
            return [
                'same' => new IntegerRange($range->start(), $range->end()),
                'unbounded' => new IntegerRange(null, null),
                'intersecting open end left' => new IntegerRange($range->start() - 2, null),
                'intersecting open end right' => new IntegerRange($range->start() + 2, null),
                'intersecting open end center' => new IntegerRange(null, $range->end() - 2),
                'intersecting open start left' => new IntegerRange(null, $range->end() - 2),
                'intersecting open start right' => new IntegerRange(null, $range->end() + 2),
                'intersecting open start center' => new IntegerRange($range->start() + 2, null),
                'disjoint open start' => new IntegerRange(null, $range->start() - 2),
                'disjoint open end' => new IntegerRange($range->end() + 2, null),
                'disjoint left' => new IntegerRange($range->start() - 10, $range->start() - 2),
                'disjoint right' => new IntegerRange($range->end() + 2, $range->end() + 10),
                'adjacent open start' => new IntegerRange(null, $range->start() - 1),
                'adjacent open end' => new IntegerRange($range->end() + 1, null),
                'adjacent left' => new IntegerRange($range->start() - 1, $range->start() - 1),
                'adjacent right' => new IntegerRange($range->end() + 1, $range->end() + 1),
                'contained' => new IntegerRange($range->start() + 1, $range->end() - 1),
                'contained same start' => new IntegerRange($range->start(), $range->end() - 1),
                'contained same end' => new IntegerRange($range->start() + 1, $range->end()),
                'containing' => new IntegerRange($range->start() - 2, $range->end() + 2),
                'containing unbounded start' => new IntegerRange(null, $range->end() + 2),
                'containing unbounded end' => new IntegerRange($range->start() - 2, null),
                'containing same start' => new IntegerRange($range->start(), $range->end() + 2),
                'containing same end' => new IntegerRange($range->start() - 2, $range->end()),
                'containing same start unbounded end' => new IntegerRange($range->start(), null),
                'containing same end unbounded start' => new IntegerRange(null, $range->end()),
            ];
        }
    }

    protected function scenarioResults(IntegerRange $range, string $method): array
    {
        return array_map(
            function ($s) use ($range, $method) {
                return $range->$method($s);
            },
            $this->createScenarios($range)
        );
    }
}
