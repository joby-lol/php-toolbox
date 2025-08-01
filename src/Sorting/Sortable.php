<?php

namespace Joby\Toolbox\Sorting;

interface Sortable
{
    public function sortByValue(): string|int|float|bool;
}