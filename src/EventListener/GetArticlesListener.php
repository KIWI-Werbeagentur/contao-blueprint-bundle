<?php

namespace Kiwi\Contao\Blueprints\EventListener;

use Kiwi\Contao\Blueprints\Controller\FrontendModule\BlueprintArticleController;
use Kiwi\Contao\Blueprints\Model\BlueprintArticleModel;
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
        if ($objPage->type == 'preview') {
            $objBlueprintArticleCollection = BlueprintArticleModel::findAll();

            $arrBlueprintArticles = [];
            foreach ($objBlueprintArticleCollection as $objBlueprintArticle)
                $arrBlueprintArticles[] = (new BlueprintArticleController($objBlueprintArticle))->generate();

            return implode("", $arrBlueprintArticles);
        }
        return null;
    }
}