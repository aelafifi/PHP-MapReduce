<?php
/**
 * PHP-MapReduce - MapReduce technique for PHP big arrays (like those which come from the database)
 * 
 * --
 * Copyright (c) 2017 Ahmed S. El-Afifi <ahmed.s.elafifi@gmail.com>
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
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * --
 * 
 * @package     PHP-MapReduce
 * @link        https://github.com/ahmedsalah94/PHP-MapReduce
 * @author      Ahmed S. El-Afifi <ahmed.s.elafifi@gmail.com>
 * @copyright   2017 Ahmed S. El-Afifi <ahmed.s.elafifi@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @version     1.0.0
 */

require_once(dirname(__FILE__).'/Emitter.php');

use MapReduce\Emitter;

class MapReduce
{

    private $data;
    private $mapper;
    private $reducer;

    public function __construct($array = [])
    {
        $this->data = $array ? $array : [];
    }

    public function setMapper($mapper)
    {
        if (!is_callable($mapper)) {
            throw new Exception("Mapper must be a callable");
        }
        $this->mapper = $mapper;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function setReducer($reducer)
    {
        if (!is_callable($reducer)) {
            throw new Exception("Reducer must be a callable");
        }
        $this->reducer = $reducer;
    }

    public function getReducer()
    {
        return $this->reducer;
    }

    public static function instance($data = [])
    {
        return new self($data);
    }

    public function map($data, $mapper = null)
    {
        if (!isset($this)) {
            // Static call
            $object = self::instance($data);
            $object->setMapper($mapper);
            return $object;
        } else {
            // Call from object
            $mapper = $data;
        }
        $this->setMapper($mapper);
        return $this;
    }

    public function reduce($reducer = null)
    {
        if (null === $this->getMapper()) {
            throw new Exception("Map First!!");
        }
        if (null !== $reducer) {
            $this->setReducer($reducer);
        }
        return $this->mapReduce();
    }

    public function getRealMapper($mapper = null)
    {
        if (null === $mapper) {
            $mapper = $this->getMapper();
        }
        if (!is_callable($mapper)) {
            throw new Exception("Mapper must be a callable");
        }
        return $mapper;
    }

    public function getRealReducer($reducer = null)
    {
        if (null === $reducer) {
            $reducer = $this->getReducer();
        }
        if (null === $reducer) {
            $reducer = function($key, $value) {
                return $value;
            };
        }
        if (!is_callable($reducer)) {
            throw new Exception("Reducer must be a callable");
        }
        return $reducer;
    }

    public static function process($data = [], $mapper = null, $reducer = null)
    {
        return MapReduce::instance($data)->map($mapper)->reduce($reducer);
    }

    public function mapReduce($mapper = null, $reducer = null)
    {
        $mapper  = $this->getRealMapper($mapper);
        $reducer = $this->getRealReducer($reducer);
        $mapEmitter = new Emitter;
        foreach ($this->data as $i => $record) {
            call_user_func($mapper, $record, $mapEmitter->getFunction(), $i);
        }
        $ret = [];
        foreach ($mapEmitter->getData() as $item) {
            $this->groupByKeyNested($ret, $item['key'], call_user_func($reducer, $item['key'], $item['data']));
        }
        return $ret;
    }

    private function groupByKeyNested(&$data, $keys = [], $value = null)
    {
        if (!is_array($keys)) {
            $keys = (array) $keys;
        }
        $key = array_shift($keys);
        
        if (count($keys) === 0) {
            $data[$key] = $value;
        } else {
            if (!array_key_exists($key, $data)) {
                $data[$key] = [];
            } elseif (!is_array($data[$key])) {
                $err_msg = 'Could not insert value %s into array because the value at key %s is no array.';
                throw new Exception(sprintf($err_msg, $value, $key));
            }
            $data[$key] = $this->groupByKeyNested($data[$key], $keys, $value);
        }

        return $data;
    }

}

?>