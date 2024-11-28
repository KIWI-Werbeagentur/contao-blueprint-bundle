<?php

namespace Kiwi\Contao\BlueprintsBundle\Controller;

use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/*
 * Blueprints preview
 * */
#[Route('/kiwi/blueprints/article', name: BlueprintArticlePreviewController::class, defaults: ['_scope' => 'frontend'])]
class BlueprintArticlePreviewController
{
    public function __invoke(Request $request)
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        if($request->attributes->get('_preview')){
            return System::getContainer()->get('Kiwi\Contao\BlueprintsBundle\Blueprint')->preview();
        }

        $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getUriForPath($request->getPathInfo()));
        throw new NotFoundHttpException($message);
    }
}
