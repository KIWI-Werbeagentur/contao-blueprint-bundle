<?php

namespace Kiwi\Contao\Blueprints;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class KiwiBlueprints extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
