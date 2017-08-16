<?php

require_once __DIR__ . '/../src/MapReduce.php';

// Data Sample
include __DIR__ . '/employees.php';

$result = MapReduce::instance($employees)
    ->map(function($record, $emit) {
        // Mapper
        $emit($record['branch'], $record['salary']);
    })
    ->reduce(function($key, $values) {
        // Reducer
        return [
            'minSalary'  => min($values),
            'maxSalary'  => max($values),
            'totalSalary' => array_sum($values),
            'avgSalary'   => round(array_sum($values) / count($values)),
            'empCount'    => count($values)
        ];
    });

print_r($result);

/*Array
(
    [Rwanda] => Array
        (
            [minSalary] => 3300
            [maxSalary] => 4100
            [totalSalary] => 11300
            [avgSalary] => 3767
            [empCount] => 3
        )

    [China] => Array
        (
            [minSalary] => 1500
            [maxSalary] => 4500
            [totalSalary] => 13700
            [avgSalary] => 2740
            [empCount] => 5
        )

    [French] => Array
        (
            [minSalary] => 3000
            [maxSalary] => 3100
            [totalSalary] => 6100
            [avgSalary] => 3050
            [empCount] => 2
        )

)*/

?>