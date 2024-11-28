<?php

namespace Kiwi\Contao\BlueprintsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class KiwiBlueprintsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
