<?php

namespace Forestry\ParameterBag;

abstract class MagicParameterBag implements ParameterBagInterface, MagicParameterBagInterface
{
    /**
     * @var array
     */
    private $params;

    /**
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->params[$key];
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->params[$key] = $value;
    }
}
