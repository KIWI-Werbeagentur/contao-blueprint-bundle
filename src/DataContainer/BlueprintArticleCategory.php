<?php

namespace Kiwi\Contao\BlueprintsBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\LayoutModel;
use Contao\PageModel;
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
        $objPageCollection = PageModel::findAll();

        return System::getContainer()->get('twig')->render('@KiwiBlueprints/backend/blueprint_article_preview.html.twig', [
            'layouts' => $objLayoutCollection,
            'pages' => $objPageCollection,
            'label' => $strLabel,
            'title' => $strTitle,
            'class' => $strClass,
            'attributes' => $strAttributes,
            'table' => $strTable,
        ]);
    }

    #[AsCallback(table: 'tl_blueprint_article_category', target: 'fields.alias.save')]
    public function generateAlias($varValue, DataContainer $objDca)
    {
        $aliasExists = static function (string $alias) use ($objDca): bool {
            return Database::getInstance()->prepare("SELECT id FROM tl_blueprint_article_category WHERE alias=? AND id!=?")->execute($alias, $objDca->id)->numRows > 0;
        };

        // Generate an alias if there is none
        if (!$varValue) {
            $varValue = System::getContainer()->get('contao.slug')->generate((string)$objDca->activeRecord->title, [], $aliasExists);
        } elseif (preg_match('/^[1-9]\d*$/', $varValue)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        } elseif ($aliasExists($varValue)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }
}
