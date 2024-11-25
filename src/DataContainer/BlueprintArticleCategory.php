<?php

namespace Kiwi\Contao\Blueprints\DataContainer;

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
        $v = $GLOBALS['TL_DCA'][$strTable]['list']['global_operations']['blueprint_article_preview'];

        if (!empty($v['route']))
        {
            $href = System::getContainer()->get('router')->generate($v['route']);
        }
        else
        {
            $href = $this->addToUrl($v['href'] ?? '');
        }

        $objLayoutCollection = LayoutModel::findAll();

        return System::getContainer()->get('twig')->render('@KiwiBlueprints/backend/blueprint_article_preview.html.twig', [
            'layouts' => $objLayoutCollection,
            'label' => $strLabel,
            'href' => $href,
            'title' => $strTitle,
            'class' => $strClass,
            'attributes' => $strAttributes,
            'table' => $strTable,
        ]);
    }
}