<?php

use \Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\System;
use Kiwi\Contao\Blueprints\DataContainer\Article;

$GLOBALS['TL_DCA']['tl_article']['list']['global_operations']['blueprint'] = [
    'href' => 'key=blueprint_article_insert&mode=create',
    'class' => 'header_blueprint',
    'attributes' => 'onclick="Backend.getScrollOffset()"',
    'icon' => 'bundles/kiwiblueprints/blueprint.svg',
    //'button_callback' => array('\Kiwi\Contao\Blueprints\DataContainer\Article', 'blueprintArticleButton')
];


$objSession = System::getContainer()->get('request_stack')->getSession();
$arrClipboard = $objSession->get('CLIPBOARD');

if (\Contao\Input::get('key') == 'blueprint_article_insert' || ($arrClipboard['tl_article']['type'] ?? false) == 'blueprint') {
    $GLOBALS['TL_DCA']['tl_article']['list']['sorting']['paste_button_callback'] = [Article::class, 'addBlueprintArticlePasteButton'];
    $GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = [Article::class, 'initPasting'];
}