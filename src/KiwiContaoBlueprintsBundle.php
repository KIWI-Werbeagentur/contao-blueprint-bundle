<?php

namespace Kiwi\ContaoBlueprintsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class KiwiContaoBlueprintsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
