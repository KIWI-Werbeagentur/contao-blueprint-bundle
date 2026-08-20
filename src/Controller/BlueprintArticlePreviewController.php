<?php

namespace Kiwi\Contao\BlueprintsBundle\Controller;

use Contao\ArticleModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Kiwi\Contao\BlueprintsBundle\Controller\FrontendModule\BlueprintArticleController;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/*
 * Blueprints preview — delivers a single blueprint article wrapped in a Turbo-Frame
 * */
#[Route('/kiwi/blueprints/article', name: BlueprintArticlePreviewController::class, defaults: ['_scope' => 'frontend'])]
class BlueprintArticlePreviewController
{
    public function __construct(
        private ContaoFramework $framework,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->framework->initialize();

        $pageId = (int) $request->query->get('page');
        $alias  = $request->query->get('alias');
        $afterArticleId = (int) $request->query->get('afterArticle', 0);

        $objPage = PageModel::findByPk($pageId);
        if (!$objPage) {
            throw new NotFoundHttpException('Page not found');
        }

        $objPage->loadDetails();
        $objPage->isBlueprintPreview = 1;

        $GLOBALS['objPage'] = $objPage;

        $request->attributes->set('pageModel', $objPage);

        $position = 0;
        if ($afterArticleId > 0) {
            $articles = ArticleModel::findPublishedByPidAndColumn($pageId, 'main');
            if ($articles) {
                $pos = 0;
                foreach ($articles as $article) {
                    $pos++;
                    if ($article->id == $afterArticleId) {
                        $position = $pos;
                        break;
                    }
                }
            }
        }

        $objBlueprintArticle = BlueprintArticleModel::findOneBy('alias', $alias);
        if (!$objBlueprintArticle) {
            throw new NotFoundHttpException('Blueprint article not found');
        }

        $objBlueprintArticle->cssID = serialize([$alias]);
        $articleHtml = (new BlueprintArticleController($objBlueprintArticle))->generate();

        $frameId = sprintf('bp-insert-%d-%d', $pageId, $position);
        $html = sprintf('<turbo-frame id="%s">%s</turbo-frame>', $frameId, $articleHtml);

        return new Response($html);
    }
}
