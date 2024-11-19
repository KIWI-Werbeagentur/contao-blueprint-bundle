<?php

use \Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_article']['list']['global_operations']['blueprint'] = [
    'href' => 'key=blueprintinsert',
    'class' => 'header_blueprint',
    'attributes' => 'onclick="Backend.getScrollOffset()"',
    'icon' => 'bundles/kiwiblueprints/blueprint.svg',
    //'button_callback' => array('\Kiwi\Contao\Blueprints\DataContainer\Article', 'blueprintArticleButton')
];

$GLOBALS['TL_DCA']['tl_article']['list']['operations']['blueprint'] = [
    'icon' => 'files/backend/blueprint.svg',
    'button_callback' => ['\Kiwi\Contao\Blueprints\DataContainer\Article', 'blueprintArticleOpButton']
];