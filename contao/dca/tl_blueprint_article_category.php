<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Kiwi\Contao\BlueprintsBundle\DataContainer\BlueprintArticleCategory;

$GLOBALS['TL_DCA']['tl_blueprint_article_category'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_blueprint_article'],
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'sortableListView' => true,
            'mode' => DataContainer::MODE_SORTED,
            'flag' => DataContainer::SORT_ASC,
            'fields' => ['title'],
        ],
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'blueprint_article_preview' => [
                'href' => 'key=blueprint_article_preview',
                'attributes' => 'target="_blank"',
                'button_callback' => [BlueprintArticleCategory::class, 'blueprintPreviewButton']
            ]
        ]
    ],

    'palettes' => [
        'default' => '{title_legend},title,alias',
    ],

    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 255],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'alias' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['rgxp' => 'alias', 'doNotCopy' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'save_callback' => [
                [\Kiwi\Contao\BlueprintsBundle\DataContainer\Article::class, 'generateAlias']
            ],
            'sql' => "varchar(255) BINARY NOT NULL default ''"
        ],
        'published' => [
            'toggle' => true,
            'filter' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_DESC,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => ['type' => 'boolean', 'default' => false]
        ],
        'sorting' => ['sql' => 'int(10) unsigned NOT NULL default 0'],
    ],
];