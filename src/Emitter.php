<?php
namespace MapReduce;

/*
 * This file is part of PHP-MapReduce
 *
 * Copyright (c) 2017 Ahmed S. El-Afifi
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Emitter
{

    private $data = [];

    public function emit($key, $value)
    {
        $serial = md5(serialize($key));
        if (!array_key_exists($serial, $this->data)) {
            $this->data[$serial] = [
                'key' => $key,
                'data' => []
            ];
        }
        $this->data[$serial]['data'][] = $value;
        return $serial;
    }

    public function getFunction()
    {
        $_this = &$this;
        return function($key, $value) use(&$_this) {
            $_this->emit($key, $value);
        };
    }

    public function getData()
    {
        return array_slice($this->data, 0);
    }

    public function resetData()
    {
        $this->data = [];
    }

}

?>