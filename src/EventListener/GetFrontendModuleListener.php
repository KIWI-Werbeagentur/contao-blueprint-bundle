<?php

namespace Kiwi\Contao\BlueprintsBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('getFrontendModule')]
class GetFrontendModuleListener
{
    /*
     * Prevent loading Frontend Modules when in Blueprint-Preview mode
     * */
    public function __invoke($objRow, $strBuffer, $objModule): ?string
    {
        global $objPage;
        if ($objPage && $objPage->type == 'blueprint_article_preview') {
            return "";
        }
        return $strBuffer;
    }
}