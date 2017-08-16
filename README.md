# PHP-MapReduce

MapReduce technique for PHP big arrays (like those which come from the database)

## How To Use:

```php
function mapper($record, $emit) { ... }
function reducer($key, $values) { ... }

$data = [[record1], [record2], [record3], ...];

// Create Object
$mapReducer = new MapReduce($data);
/* OR */
$mapReducer = MapReduce::instance($data);

// Map-Reduce
$newData = $mapReducer->mapReduce('mapper', 'reducer');
/* OR */
$newData = $mapReducer->map('mapper')->reduce('reducer');

// One Step
$newData = MapReduce::process($data, 'mapper', 'reducer');
```

## Examples:

Data Sample:

```php
$employees = [
    ['id' => 1,  'name' => 'Althea',  'gender' => 'Female', 'branch' => 'Rwanda', 'salary' => '3900'],
    ['id' => 2,  'name' => 'John',    'gender' => 'Male',   'branch' => 'China',  'salary' => '3100'],
    ['id' => 3,  'name' => 'Noah',    'gender' => 'Male',   'branch' => 'China',  'salary' => '1900'],
    ['id' => 4,  'name' => 'Steven',  'gender' => 'Male',   'branch' => 'French', 'salary' => '3100'],
    ['id' => 5,  'name' => 'Helen',   'gender' => 'Female', 'branch' => 'China',  'salary' => '1500'],
    ['id' => 6,  'name' => 'Madison', 'gender' => 'Female', 'branch' => 'Rwanda', 'salary' => '4100'],
    ['id' => 7,  'name' => 'Richard', 'gender' => 'Male',   'branch' => 'China',  'salary' => '2700'],
    ['id' => 8,  'name' => 'Jesse',   'gender' => 'Female', 'branch' => 'French', 'salary' => '3000'],
    ['id' => 9,  'name' => 'Walker',  'gender' => 'Male',   'branch' => 'Rwanda', 'salary' => '3300'],
    ['id' => 10, 'name' => 'Brandon', 'gender' => 'Male',   'branch' => 'China',  'salary' => '4500']
];
```

#### Example 1: Salary Report

```php
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
```

Output:

```php
Array
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

)
```

#### Example 2: Multi-level Mapping

```php
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
```

Output:

```php
Array
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

)
```

## To-Do List:
- [ ] Add comments to code
- [ ] Add more examples
- [ ] Map DISTINCT

## License and Acknowledgements

Copyright 2017 Ahmed S. El-Afifi <ahmed.s.elafifi@gmail.com>

Licensed under the MIT License
