<?php

namespace Kiwi\Contao\Blueprints\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/kiwi/blueprints/article', name: BlueprintArticlePreviewController::class, defaults: ['_scope' => 'frontend'])]
class BlueprintArticlePreviewController
{
    public function __construct(
    ) {
    }

    public function __invoke(Request $request)
    {
        return new Response('bufiwebebuoe');
    }
}
