<?php

namespace Forestry\ParameterBag\Exception;

class ChangesNotAllowedException extends \Exception
{
    public static function create()
    {
        return new self(sprintf("Changes to immutable parameter bag are not allowed"));
    }
}
