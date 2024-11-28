<?php

namespace Kiwi\Contao\BlueprintsBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\LayoutModel;
use Contao\System;

class BlueprintArticleCategory extends Backend
{
    /*
     * Create global Button to init blueprint inserting
     * */
    #[AsCallback(table: 'tl_blueprint_article_category', target: 'list:global_operations.blueprint.button')]
    public function blueprintPreviewButton(string|null $strHref, string|null $strLabel, string|null $strTitle, string|null $strClass, string|null $strAttributes, string|null $strTable)
    {
        $objLayoutCollection = LayoutModel::findAll();

        return System::getContainer()->get('twig')->render('@KiwiBlueprintsBundle/backend/blueprint_article_preview.html.twig', [
            'layouts' => $objLayoutCollection,
            'label' => $strLabel,
            'title' => $strTitle,
            'class' => $strClass,
            'attributes' => $strAttributes,
            'table' => $strTable,
        ]);
    }
}