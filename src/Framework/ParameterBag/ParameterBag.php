<?php

namespace Forestry\Framework\ParameterBag;

class ParameterBag
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
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    public function offsetGet($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            $param = $this->params[$key];
            if (is_string($param) && strlen($param) === 0) {
                return $default;
            }

            return $param;
        }

        if (isset($default)) {
            return $default;
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
