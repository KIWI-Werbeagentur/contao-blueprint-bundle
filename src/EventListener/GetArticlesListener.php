<?php

namespace Kiwi\Contao\BlueprintsBundle\EventListener;

use Contao\ArticleModel;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;

#[AsHook('getArticles')]
class GetArticlesListener
{
    /*
     * Insert Turbo-Frames between regular articles when CLP mode is active
     * */
    public function __invoke(int $pageId, string $column): string|null
    {
        if (!Input::get('_clp') || $column !== 'main') {
            return null;
        }

        global $objPage;
        if (!$objPage) {
            return null;
        }

        $articles = ArticleModel::findPublishedByPidAndColumn($pageId, $column);

        if (!$articles) {
            return sprintf('<turbo-frame id="bp-insert-%d-0"></turbo-frame>', $pageId);
        }

        $html = sprintf('<turbo-frame id="bp-insert-%d-0"></turbo-frame>', $pageId);
        $position = 0;

        foreach ($articles as $article) {
            $article->cssID = unserialize($article->cssID);
            $html .= Controller::getArticle($article);

            $position++;
            $html .= sprintf('<turbo-frame id="bp-insert-%d-%d"></turbo-frame>', $pageId, $position);
        }

        return $html;
    }
}
