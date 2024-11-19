<?php

use Contao\DataContainer;
use Contao\DC_Table;

$this->loadDataContainer('tl_article');

$GLOBALS['TL_DCA']['tl_blueprint_article']['select'] = $GLOBALS['TL_DCA']['tl_article']['select'];
$GLOBALS['TL_DCA']['tl_blueprint_article']['palettes'] = $GLOBALS['TL_DCA']['tl_article']['palettes'];
$GLOBALS['TL_DCA']['tl_blueprint_article']['subpalettes'] = $GLOBALS['TL_DCA']['tl_article']['subpalettes'];
$GLOBALS['TL_DCA']['tl_blueprint_article']['fields'] = $GLOBALS['TL_DCA']['tl_article']['fields'];


$GLOBALS['TL_DCA']['tl_blueprint_article'] += [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_blueprint_article_category',
        'ctable' => ['tl_content'],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'sql' =>
            [
                'keys' => [
                    'id' => 'primary',
                    'tstamp' => 'index',
                    'alias' => 'index',
                    'published,start,stop' => 'index'
                ]
            ]
    ],
    'list' => [
        'sorting' =>
            [
                'mode' => DataContainer::MODE_PARENT,
                'fields' => ['sorting'],
                'headerFields' => ['title'],
                'disableGrouping' => true
            ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s'
        ]
    ]
];