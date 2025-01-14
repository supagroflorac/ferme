<?php

namespace Ferme;

use Exception;
use ArrayAccess;
use Iterator;
use Countable;

abstract class Collection implements ArrayAccess, Iterator, Countable
{
    protected $list = null;

    public function __construct()
    {
        $this->list = array();
    }

    public function add($key, $object)
    {
        $this->list[$key] = $object;
    }

    public function delete($key)
    {
        if (!isset($this->list[$key])) {
            throw new Exception(
                "Impossible de supprimer l'élément' $key. Il n'existe pas.",
                1
            );
        }
        $this->list[$key]->delete();
        unset($this->list[$key]);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->list[] = $value;
            return;
        }
        $this->list[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->list[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->list[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->list[$offset]) ? $this->list[$offset] : null;
    }

    public function exist($key)
    {
        return $this->offsetExists($key);
    }

    public function count()
    {
        return count($this->list);
    }

    /*************************************************************************
     * Iterator
     ************************************************************************/
    public function rewind()
    {
        return reset($this->list);
    }
    public function current()
    {
        return current($this->list);
    }
    public function key()
    {
        return key($this->list);
    }
    public function valid()
    {
        return isset($this->list[$this->key()]);
    }
    public function next()
    {
        return next($this->list);
    }

    public function search(string $string = '*'): array
    {
        if ($string === '*' or $string === '') {
            return $this->list;
        }

        $selected = array();
        foreach ($this->list as $name => $object) {
            if (strstr($name, $string)) {
                $selected[$name] = $object;
            }
        }

        return $selected;
    }

    public function searchNoCaseType(string $string = '*'): array
    {
        if ($string === '*' or $string === '') {
            return $this->list;
        }

        $selected = array();
        foreach ($this->list as $name => $object) {
            if (strstr(strtolower($name), strtolower($string))) {
                $selected[$name] = $object;
            }
        }

        return $selected;
    }
}
