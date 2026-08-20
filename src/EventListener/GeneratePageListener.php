<?php

namespace Kiwi\Contao\BlueprintsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;

#[AsHook('generatePage')]
class GeneratePageListener
{
    /*
     * Inject Turbo.js into the frontend when CLP mode is active
     * so that <turbo-frame> elements work in the CLP sidebar iframe
     * */
    public function __invoke(): void
    {
        if (Input::get('_clp')) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/kiwiblueprints/turbo.min.js|static';
        }
    }
}
