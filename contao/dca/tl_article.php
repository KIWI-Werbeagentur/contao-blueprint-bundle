<?php

use \Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\System;
use Kiwi\Contao\BlueprintsBundle\DataContainer\Article;
use Kiwi\Contao\BlueprintsBundle\Drivers\DC_Table_Blueprint;

$GLOBALS['TL_DCA']['tl_article']['list']['global_operations']['blueprint'] = [
    'href' => 'key=blueprint_article_insert&mode=create',
    'class' => 'header_blueprint',
    'attributes' => 'onclick="Backend.getScrollOffset()"',
    'icon' => 'bundles/kiwiblueprints/blueprint.svg',
];

$GLOBALS['TL_DCA']['tl_article']['fields']['template'] = [
    'sql' => "int(10) unsigned NOT NULL default 0"
];

$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = [Article::class, 'initPasting'];

$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = [Article::class, 'onCopyListener'];

$GLOBALS['TL_DCA']['tl_article']['config']['dataContainer'] = DC_Table_Blueprint::class;