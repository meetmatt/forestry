<?php

namespace Forestry\ParameterBag;

interface MagicParameterBagInterface
{
    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key);

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value);
}
