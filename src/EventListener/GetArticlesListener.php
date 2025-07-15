<?php

namespace Kiwi\Contao\BlueprintsBundle\EventListener;

use Kiwi\Contao\BlueprintsBundle\Controller\FrontendModule\BlueprintArticleController;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('getArticles')]
class GetArticlesListener
{
    /*
     * Load Articles from tl_blueprint_article in Blueprint-Preview mode
     * */
    public function __invoke(int $pageId, string $column): string|null
    {
        global $objPage;
        if ($objPage->isBlueprintPreview && $column == 'main') {
            $objBlueprintArticleCollection = BlueprintArticleModel::findAll();
            if(!$objBlueprintArticleCollection) return null;

            $arrBlueprintArticles = [];
            foreach ($objBlueprintArticleCollection as $objBlueprintArticle) {
                $objBlueprintArticle->cssID = serialize([$objBlueprintArticle->alias]);
                $arrBlueprintArticles[] = (new BlueprintArticleController($objBlueprintArticle))->generate();
            }

            return implode("", $arrBlueprintArticles);
        }
        return null;
    }
}