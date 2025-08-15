<?php

use Contao\Database;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Image;
use Contao\Input;
use Contao\System;
use Kiwi\Contao\BlueprintsBundle\DataContainer\Article;

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
        'onsubmit_callback' => [function($dc){
            Database::getInstance()->prepare("UPDATE tl_blueprint_article SET template = ?  WHERE id=?")->execute($dc->id, $dc->id);
        }],
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

$GLOBALS['TL_DCA']['tl_blueprint_article']['fields']['template']['eval'] = ['alwaysSave' => true];

try {
    $objSession = System::getContainer()->get('request_stack')->getSession();

    $arrClipboard = $objSession->get('CLIPBOARD');

    if ($arrClipboard['tl_article'] ?? false) {
        $strPid = Input::get('id');
        $GLOBALS['TL_DCA']['tl_blueprint_article']['list']['global_operations']['article'] = [
            'href' => "key=article_insert&act=copy&pid={$strPid}&mode=2",
            'class' => 'header_blueprint',
            'attributes' => 'onclick = "Backend.getScrollOffset()"',
            'icon' => "new"
        ];
    }
} catch(Exception $e){}
