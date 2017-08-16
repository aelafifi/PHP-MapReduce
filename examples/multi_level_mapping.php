<?php

require_once __DIR__ . '/../src/MapReduce.php';

// Data Sample
include __DIR__ . '/employees.php';

$result = MapReduce::instance($employees)
    ->map(function($record, $emit) {
        // Mapper
        $nested_keys = [$record['branch'], $record['gender']];
        $emit($nested_keys, $record['name']);
    })
    ->reduce(function($key, $values) {
        // Reducer
        return implode(', ', $values);
    });

print_r($result);

/*Array
(
    [Rwanda] => Array
        (
            [Female] => Althea, Madison
            [Male] => Walker
        )

    [China] => Array
        (
            [Male] => John, Noah, Richard, Brandon
            [Female] => Helen
        )

    [French] => Array
        (
            [Male] => Steven
            [Female] => Jesse
        )

)*/

?>