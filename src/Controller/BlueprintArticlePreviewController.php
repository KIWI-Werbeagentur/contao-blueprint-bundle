<?php

namespace Kiwi\Contao\BlueprintsBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Kiwi\Contao\BlueprintsBundle\Blueprint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/*
 * Blueprints preview
 * */
#[Route('/kiwi/blueprints/article', name: BlueprintArticlePreviewController::class, defaults: ['_scope' => 'frontend'])]
class BlueprintArticlePreviewController
{
    public function __construct(
        private Blueprint $blueprint,
        private ContaoFramework $framework,
    ) {
    }

    public function __invoke(Request $request): never
    {
        $this->framework->initialize();

        if ($request->attributes->get('_preview')) {
            $this->blueprint->preview();
        }

        $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getUriForPath($request->getPathInfo()));
        throw new NotFoundHttpException($message);
    }
}
