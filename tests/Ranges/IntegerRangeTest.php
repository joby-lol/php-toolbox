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
        // fully unbounded contains anything
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

    public function testAdjacentRightOf()
    {
        // fully bounded only abuts the end of things that are adjacent left
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
                'adjacent open start' => true,
                'adjacent open end' => false,
                'adjacent left' => true,
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
                'adjacentRightOf',
            )
        );
        // open start can't abut the end of anything
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
                'adjacent open end' => false,
                'adjacent bounded end' => false,
                'disjoint open end' => false,
                'disjoint bounded end' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'adjacentRightOf',
            )
        );
        // open end only abuts the end of things that are adjacent left
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
                'adjacent open start' => true,
                'adjacent bounded start' => true,
                'disjoint open start' => false,
                'disjoint bounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'adjacentRightOf',
            )
        );
        // fully unbounded can't abut anything
        $this->assertEquals(
            [
                'same' => false,
                'open start' => false,
                'open end' => false,
                'bounded' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'adjacentRightOf',
            )
        );
    }

    public function testAdjacentLeftOf()
    {
        // fully bounded only abuts the start of things that are adjacent right
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
                'adjacentLeftOf',
            )
        );
        // open start only abuts the start of things that are adjacent right
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
                'adjacentLeftOf',
            )
        );
        // open end can't abut the start of anything
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
                'adjacentLeftOf',
            )
        );
        // fully unbounded can't abut anything
        $this->assertEquals(
            [
                'same' => false,
                'open start' => false,
                'open end' => false,
                'bounded' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'adjacentLeftOf',
            )
        );
    }

    public function testAdjacent()
    {
        // fully bounded only abuts things that are adjacent
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
                'adjacent open start' => true,
                'adjacent open end' => true,
                'adjacent left' => true,
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
                'adjacent',
            )
        );
        // open start abuts adjacent right
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
                'adjacent',
            )
        );
        // open end abuts adjacent left
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
                'adjacent open start' => true,
                'adjacent bounded start' => true,
                'disjoint open start' => false,
                'disjoint bounded start' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'adjacent',
            )
        );
        // fully unbounded can't abut anything
        $this->assertEquals(
            [
                'same' => false,
                'open start' => false,
                'open end' => false,
                'bounded' => false,
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'adjacent',
            )
        );
    }

    public function testBooleanAnd()
    {
        // fully bounded
        $this->assertEquals(
            [
                'same' => '10,20',
                'unbounded' => '10,20',
                'intersecting open end left' => '10,20',
                'intersecting open end right' => '11,20',
                'intersecting open end center' => '10,20',
                'intersecting open start left' => '10,19',
                'intersecting open start right' => '10,20',
                'intersecting open start center' => '10,20',
                'disjoint open start' => null,
                'disjoint open end' => null,
                'disjoint left' => null,
                'disjoint right' => null,
                'adjacent open start' => null,
                'adjacent open end' => null,
                'adjacent left' => null,
                'adjacent right' => null,
                'contained' => '11,19',
                'contained same start' => '10,19',
                'contained same end' => '11,20',
                'containing' => '10,20',
                'containing unbounded start' => '10,20',
                'containing unbounded end' => '10,20',
                'containing same start' => '10,20',
                'containing same end' => '10,20',
                'containing same start unbounded end' => '10,20',
                'containing same end unbounded start' => '10,20',
            ],
            $this->scenarioResults(
                new IntegerRange(10, 20),
                'booleanAnd',
            )
        );
        // open start
        $this->assertEquals(
            [
                'same' => 'null,20',
                'unbounded' => 'null,20',
                'intersecting open start left' => 'null,19',
                'intersecting open start right' => 'null,20',
                'intersecting bounded start left' => '9,19',
                'intersecting bounded start right' => '11,20',
                'intersecting bounded start center' => '10,20',
                'intersecting open end same' => '20,20',
                'intersecting open end left' => '19,20',
                'intersecting bounded end same' => '20,20',
                'intersecting bounded end left' => '19,20',
                'adjacent open end' => null,
                'adjacent bounded end' => null,
                'disjoint open end' => null,
                'disjoint bounded end' => null,
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'booleanAnd',
            )
        );
        // open end
        $this->assertEquals(
            [
                'same' => '10,null',
                'unbounded' => '10,null',
                'intersecting open end left' => '10,null',
                'intersecting open end right' => '11,null',
                'intersecting bounded end left' => '10,19',
                'intersecting bounded end right' => '11,21',
                'intersecting bounded end center' => '10,20',
                'intersecting open start same' => '10,10',
                'intersecting open start right' => '10,11',
                'intersecting bounded start same' => '10,10',
                'intersecting bounded start right' => '10,11',
                'adjacent open start' => null,
                'adjacent bounded start' => null,
                'disjoint open start' => null,
                'disjoint bounded start' => null,
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'booleanAnd',
            )
        );
        // fully unbounded
        $this->assertEquals(
            [
                'same' => 'null,null',
                'open start' => 'null,20',
                'open end' => '10,null',
                'bounded' => '10,20',
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'booleanAnd',
            )
        );
    }

    public function testBooleanOr()
    {
        // fully bounded
        $this->assertEquals(
            [
                'same' => '10,20',
                'unbounded' => 'null,null',
                'intersecting open end left' => '9,null',
                'intersecting open end right' => '10,null',
                'intersecting open end center' => '10,null',
                'intersecting open start left' => 'null,20',
                'intersecting open start right' => 'null,21',
                'intersecting open start center' => 'null,20',
                'disjoint open start' => 'null,8;10,20',
                'disjoint open end' => '10,20;22,null',
                'disjoint left' => '-2,8;10,20',
                'disjoint right' => '10,20;22,32',
                'adjacent open start' => 'null,20',
                'adjacent open end' => '10,null',
                'adjacent left' => '-1,20',
                'adjacent right' => '10,31',
                'contained' => '10,20',
                'contained same start' => '10,20',
                'contained same end' => '10,20',
                'containing' => '9,21',
                'containing unbounded start' => 'null,21',
                'containing unbounded end' => '9,null',
                'containing same start' => '10,21',
                'containing same end' => '9,20',
                'containing same start unbounded end' => '10,null',
                'containing same end unbounded start' => 'null,20',
            ],
            $this->scenarioResults(
                new IntegerRange(10, 20),
                'booleanOr',
            )
        );
        // open start
        $this->assertEquals(
            [
                'same' => 'null,20',
                'unbounded' => 'null,null',
                'intersecting open start left' => 'null,20',
                'intersecting open start right' => 'null,21',
                'intersecting bounded start left' => 'null,20',
                'intersecting bounded start right' => 'null,21',
                'intersecting bounded start center' => 'null,20',
                'intersecting open end same' => 'null,null',
                'intersecting open end left' => 'null,null',
                'intersecting bounded end same' => 'null,30',
                'intersecting bounded end left' => 'null,29',
                'adjacent open end' => 'null,null',
                'adjacent bounded end' => 'null,30',
                'disjoint open end' => 'null,20;22,null',
                'disjoint bounded end' => 'null,20;22,32',
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'booleanOr',
            )
        );
        // open end
        $this->assertEquals(
            [
                'same' => '10,null',
                'unbounded' => 'null,null',
                'intersecting open end left' => '9,null',
                'intersecting open end right' => '10,null',
                'intersecting bounded end left' => '9,null',
                'intersecting bounded end right' => '10,null',
                'intersecting bounded end center' => '10,null',
                'intersecting open start same' => 'null,null',
                'intersecting open start right' => 'null,null',
                'intersecting bounded start same' => '0,null',
                'intersecting bounded start right' => '1,null',
                'adjacent open start' => 'null,null',
                'adjacent bounded start' => '0,null',
                'disjoint open start' => 'null,8;10,null',
                'disjoint bounded start' => '-2,8;10,null',
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'booleanOr',
            )
        );
        // fully unbounded
        $this->assertEquals(
            [
                'same' => 'null,null',
                'open start' => 'null,null',
                'open end' => 'null,null',
                'bounded' => 'null,null',
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'booleanOr',
            )
        );
    }

    public function testBooleanNot()
    {
        // fully bounded
        $this->assertEquals(
            [
                'same' => '',
                'unbounded' => '',
                'intersecting open end left' => '',
                'intersecting open end right' => '10,10',
                'intersecting open end center' => '',
                'intersecting open start left' => '20,20',
                'intersecting open start right' => '',
                'intersecting open start center' => '',
                'disjoint open start' => '10,20',
                'disjoint open end' => '10,20',
                'disjoint left' => '10,20',
                'disjoint right' => '10,20',
                'adjacent open start' => '10,20',
                'adjacent open end' => '10,20',
                'adjacent left' => '10,20',
                'adjacent right' => '10,20',
                'contained' => '10,10;20,20',
                'contained same start' => '20,20',
                'contained same end' => '10,10',
                'containing' => '',
                'containing unbounded start' => '',
                'containing unbounded end' => '',
                'containing same start' => '',
                'containing same end' => '',
                'containing same start unbounded end' => '',
                'containing same end unbounded start' => '',
            ],
            $this->scenarioResults(
                new IntegerRange(10, 20),
                'booleanNot',
            )
        );
        // open start
        $this->assertEquals(
            [
                'same' => '',
                'unbounded' => '',
                'intersecting open start left' => '20,20',
                'intersecting open start right' => '',
                'intersecting bounded start left' => 'null,8;20,20',
                'intersecting bounded start right' => 'null,10',
                'intersecting bounded start center' => 'null,9',
                'intersecting open end same' => 'null,19',
                'intersecting open end left' => 'null,18',
                'intersecting bounded end same' => 'null,19',
                'intersecting bounded end left' => 'null,18',
                'adjacent open end' => 'null,20',
                'adjacent bounded end' => 'null,20',
                'disjoint open end' => 'null,20',
                'disjoint bounded end' => 'null,20',
            ],
            $this->scenarioResults(
                new IntegerRange(null, 20),
                'booleanNot',
            )
        );
        // open end
        $this->assertEquals(
            [
                'same' => '',
                'unbounded' => '',
                'intersecting open end left' => '',
                'intersecting open end right' => '10,10',
                'intersecting bounded end left' => '20,null',
                'intersecting bounded end right' => '10,10;22,null',
                'intersecting bounded end center' => '21,null',
                'intersecting open start same' => '11,null',
                'intersecting open start right' => '12,null',
                'intersecting bounded start same' => '11,null',
                'intersecting bounded start right' => '12,null',
                'adjacent open start' => '10,null',
                'adjacent bounded start' => '10,null',
                'disjoint open start' => '10,null',
                'disjoint bounded start' => '10,null',
            ],
            $this->scenarioResults(
                new IntegerRange(10, null),
                'booleanNot',
            )
        );
        // fully unbounded
        $this->assertEquals(
            [
                'same' => '',
                'open start' => '21,null',
                'open end' => 'null,9',
                'bounded' => 'null,9;21,null',
            ],
            $this->scenarioResults(
                new IntegerRange(null, null),
                'booleanNot',
            )
        );
    }

    protected function createScenarios(IntegerRange $range): array
    {
        if (is_null($range->start()) && is_null($range->end())) {
            // scenarios for fully open range
            return [
                'same' => new IntegerRange(null, null),
                'open start' => new IntegerRange(null, 20),
                'open end' => new IntegerRange(10, null),
                'bounded' => new IntegerRange(10, 20),
            ];
        } elseif (is_null($range->start())) {
            // scenarios for unbounded start
            return [
                'same' => new IntegerRange(null, $range->end()),
                'unbounded' => new IntegerRange(null, null),
                'intersecting open start left' => new IntegerRange(null, $range->end() - 1),
                'intersecting open start right' => new IntegerRange(null, $range->end() + 1),
                'intersecting bounded start left' => new IntegerRange($range->end() - 11, $range->end() - 1),
                'intersecting bounded start right' => new IntegerRange($range->end() - 9, $range->end() + 1),
                'intersecting bounded start center' => new IntegerRange($range->end() - 10, $range->end()),
                'intersecting open end same' => new IntegerRange($range->end(), null),
                'intersecting open end left' => new IntegerRange($range->end() - 1, null),
                'intersecting bounded end same' => new IntegerRange($range->end(), $range->end() + 10),
                'intersecting bounded end left' => new IntegerRange($range->end() - 1, $range->end() + 9),
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
                'intersecting open end left' => new IntegerRange($range->start() - 1, null),
                'intersecting open end right' => new IntegerRange($range->start() + 1, null),
                'intersecting bounded end left' => new IntegerRange($range->start() - 1, $range->start() + 9),
                'intersecting bounded end right' => new IntegerRange($range->start() + 1, $range->start() + 11),
                'intersecting bounded end center' => new IntegerRange($range->start(), $range->start() + 10),
                'intersecting open start same' => new IntegerRange(null, $range->start()),
                'intersecting open start right' => new IntegerRange(null, $range->start() + 1),
                'intersecting bounded start same' => new IntegerRange($range->start() - 10, $range->start()),
                'intersecting bounded start right' => new IntegerRange($range->start() - 9, $range->start() + 1),
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
                'intersecting open end left' => new IntegerRange($range->start() - 1, null),
                'intersecting open end right' => new IntegerRange($range->start() + 1, null),
                'intersecting open end center' => new IntegerRange($range->start(), null),
                'intersecting open start left' => new IntegerRange(null, $range->end() - 1),
                'intersecting open start right' => new IntegerRange(null, $range->end() + 1),
                'intersecting open start center' => new IntegerRange(null, $range->end()),
                'disjoint open start' => new IntegerRange(null, $range->start() - 2),
                'disjoint open end' => new IntegerRange($range->end() + 2, null),
                'disjoint left' => new IntegerRange($range->start() - 12, $range->start() - 2),
                'disjoint right' => new IntegerRange($range->end() + 2, $range->end() + 12),
                'adjacent open start' => new IntegerRange(null, $range->start() - 1),
                'adjacent open end' => new IntegerRange($range->end() + 1, null),
                'adjacent left' => new IntegerRange($range->start() - 11, $range->start() - 1),
                'adjacent right' => new IntegerRange($range->end() + 1, $range->end() + 11),
                'contained' => new IntegerRange($range->start() + 1, $range->end() - 1),
                'contained same start' => new IntegerRange($range->start(), $range->end() - 1),
                'contained same end' => new IntegerRange($range->start() + 1, $range->end()),
                'containing' => new IntegerRange($range->start() - 1, $range->end() + 1),
                'containing unbounded start' => new IntegerRange(null, $range->end() + 1),
                'containing unbounded end' => new IntegerRange($range->start() - 1, null),
                'containing same start' => new IntegerRange($range->start(), $range->end() + 1),
                'containing same end' => new IntegerRange($range->start() - 1, $range->end()),
                'containing same start unbounded end' => new IntegerRange($range->start(), null),
                'containing same end unbounded start' => new IntegerRange(null, $range->end()),
            ];
        }
    }

    protected function scenarioResults(IntegerRange $range, string $method): array
    {
        return array_map(
            function ($s) use ($range, $method) {
                $result = $range->$method($s);
                if ($result instanceof IntegerRange) {
                    $result = ($result->start() ?? 'null') . ',' . ($result->end() ?? 'null');
                }
                if (is_array($result)) {
                    $result = implode(';', array_map(
                        function ($r) {
                            return ($r->start() ?? 'null') . ',' . ($r->end() ?? 'null');
                        },
                        $result
                    ));
                }
                return $result;
            },
            $this->createScenarios($range)
        );
    }
}
