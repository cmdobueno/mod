<?php

namespace Cmdobueno\Mod\Exceptions;

use Exception;

class MissingModuleException extends Exception
{
    public function __construct($missing)
    {
        parent::__construct(
            $missing
        );
    }
}
