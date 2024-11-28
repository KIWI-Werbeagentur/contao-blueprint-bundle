<?php

use \Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\System;
use Kiwi\Contao\BlueprintsBundle\DataContainer\Article;

$GLOBALS['TL_DCA']['tl_article']['list']['global_operations']['blueprint'] = [
    'href' => 'key=blueprint_article_insert&mode=create',
    'class' => 'header_blueprint',
    'attributes' => 'onclick="Backend.getScrollOffset()"',
    'icon' => 'bundles/kiwiblueprints/blueprint.svg',
    //'button_callback' => array('\Kiwi\Contao\BlueprintsBundle\DataContainer\Article', 'blueprintArticleButton')
];

$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = [Article::class, 'initPasting'];