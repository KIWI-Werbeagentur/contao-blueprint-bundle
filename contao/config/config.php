<?php

use Contao\System;
use Kiwi\Contao\Blueprints\Blueprint;
use Kiwi\Contao\Blueprints\Model\BlueprintArticleCategoryModel;
use Kiwi\Contao\Blueprints\Model\BlueprintArticleModel;
use Symfony\Component\HttpFoundation\Request;

if (System::getContainer()->get('contao.routing.scope_matcher')
    ->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))
)
{
    $GLOBALS['TL_CSS'][] = 'bundles/kiwiblueprints/blueprint.css';
}

$GLOBALS['BE_MOD']['design']['blueprint_article'] = [
    'tables' => ['tl_blueprint_article_category', 'tl_blueprint_article', 'tl_content'],
    'blueprint_article_preview' => [Blueprint::class, 'preview'],
    'blueprint_article_insert' => [Blueprint::class, 'insert']
];

$GLOBALS['TL_MODELS']['tl_blueprint_article'] = BlueprintArticleModel::class;
$GLOBALS['TL_MODELS']['tl_blueprint_article_category'] = BlueprintArticleCategoryModel::class;

$GLOBALS['TL_PERMISSIONS'][] = 'blueprint.article';