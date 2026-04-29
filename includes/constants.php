<?php

declare(strict_types=1);

/** @var list<int> */
const YEAR_GROUP_VALUES = [2026, 2027, 2028, 2029];

/** @var list<string> */
const MAJOR_VALUES = ['BA', 'ECON', 'LAW', 'CS', 'MIS', 'CE', 'EE', 'ME', 'MT'];

function year_group_is_valid(int $y): bool
{
    return in_array($y, YEAR_GROUP_VALUES, true);
}

function major_is_valid(string $m): bool
{
    return in_array($m, MAJOR_VALUES, true);
}
