<?php

namespace Kiwi\ContaoBlueprintsBundle\EventListener;

use Contao\System;
use Kiwi\ContaoBlueprintsBundle\Controller\FrontendModule\BlueprintArticleController;
use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('compileArticle')]
class CompileArticleListener
{
    /*
     * Load Contents from tl_blueprint_article in Blueprint-Preview mode
     * */
    public function __invoke(&$objTemplate, $arrData, $objArticleController): void
    {
        if ($objArticleController instanceof BlueprintArticleController) {
            $arrElements = [];

            $objCte = ContentModel::findPublishedByPidAndTable($objArticleController->id, 'tl_blueprint_article');

            if ($objCte !== null) {
                while ($objCte->next()) {
                    $arrElements[] = $objArticleController::getContentElement($objCte->current(), $objArticleController->strColumn ?? 'main');
                }
            }

            $objTemplate->teaser = $objArticleController->teaser;
            $objTemplate->elements = $arrElements;
        }
    }
}