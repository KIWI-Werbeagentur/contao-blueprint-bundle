<?php

use \Contao\CoreBundle\DataContainer\PaletteManipulator;
use Kiwi\Contao\Blueprints\DataContainer\Article;

$GLOBALS['TL_DCA']['tl_article']['list']['global_operations']['blueprint'] = [
    'href' => 'key=blueprint_article_insert&mode=create',
    'class' => 'header_blueprint',
    'attributes' => 'onclick="Backend.getScrollOffset()"',
    'icon' => 'bundles/kiwiblueprints/blueprint.svg',
    //'button_callback' => array('\Kiwi\Contao\Blueprints\DataContainer\Article', 'blueprintArticleButton')
];

if (\Contao\Input::get('key') == 'blueprint_article_insert') {
    $GLOBALS['TL_DCA']['tl_article']['list']['sorting']['paste_button_callback'] = [Article::class, 'addBlueprintArticlePasteButton'];
    $GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = [Article::class, 'initPasting'];
}