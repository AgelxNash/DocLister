<?php namespace Helpers;

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess{
    protected $data = array();

    public function __construct(array $data = array()){
        $this->data = $data;
    }
    public function create(array $data = array()){
        return new static($data);
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
    public function map(\Closure $func)
    {
        return $this->create(array_map($func, $this->data));
    }
    public function filter(\Closure $p)
    {
        return $this->create(array_filter($this->data, $p));
    }
    public function forAll(\Closure $p)
    {
        foreach ($this->data as $key => $element) {
            if ( ! $p($key, $element)) {
                return false;
            }
        }
        return true;
    }
    public function partition(\Closure $p)
    {
        $matches = $noMatches = array();
        foreach ($this->data as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }
        return array($this->create($matches), $this->create($noMatches));
    }
    public function slice($offset, $length = null)
    {
        return array_slice($this->data, $offset, $length, true);
    }
    public function isEmpty()
    {
        return empty($this->data);
    }
    public function clear(){
        $this->data = array();
        return $this;
    }
    public function append($value) {
        $this->data[] = $value;
        return $this;
    }
    public function add($data, $id = null){
        if((is_int($id) || is_string($id)) && $id !== ''){
            $this->data[$key] = $data;
        }else{
            $this->append($data);
        }
        return $this;
    }
    public function count(){
        return count($this->data);
    }
    public function get($id){
        $out = null;
        if(is_scalar($id) && $id!='' && isset($this->data[$id])){
            $out = $this->data[$id];
        }
        return $out;
    }
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
    public function first()
    {
        return reset($this->data);
    }

    public function last()
    {
        return end($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function next()
    {
        return next($this->data);
    }

    public function current()
    {
        return current($this->data);
    }

    public function remove($key)
    {
        if ( ! isset($this->data[$key]) && ! array_key_exists($key, $this->data)) {
            return null;
        }
        $removed = $this->data[$key];
        unset($this->data[$key]);
        return $removed;
    }

    public function removeElement($element)
    {
        $key = array_search($element, $this->data, true);
        if ($key === false) {
            return false;
        }
        unset($this->data[$key]);
        return true;
    }

    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }
    public function dump(){
        return var_dump($this->data);
    }
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        if ( ! isset($offset)) {
            return $this->add($value);
        }
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }

    public function containsKey($key)
    {
        return isset($this->data[$key]) || array_key_exists($key, $this->data);
    }

    public function contains($element)
    {
        return in_array($element, $this->data, true);
    }

    public function exists(Closure $p)
    {
        foreach ($this->data as $key => $element) {
            if ($p($key, $element)) {
                return true;
            }
        }
        return false;
    }

    public function indexOf($element)
    {
        return array_search($element, $this->data, true);
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }
    public function getValues()
    {
        return array_values($this->data);
    }
}