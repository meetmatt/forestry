<?php

namespace Forestry\ParameterBag;

interface ParameterBagInterface
{
    /**
     * @param string $key
     * @return bool
     */
    public function offsetExists($key);

    /**
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value);
}
