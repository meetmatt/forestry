<?php

namespace Forestry\ParameterBag;
use Forestry\ParameterBag\Exception\ChangesNotAllowedException;

/**
 * Parameter bag which members can only be set via constructor
 * No director changes to members permitted
 */
class ImmutableParameterBag extends MagicParameterBag
{
    /**
     * @param string $key
     * @param mixed $value
     * @throws ChangesNotAllowedException
     */
    public function offsetSet($key, $value)
    {
        throw ChangesNotAllowedException::create();
    }
}
