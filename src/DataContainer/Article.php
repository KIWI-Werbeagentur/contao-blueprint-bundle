<?php

namespace Kiwi\Contao\Blueprints\DataContainer;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\Image;
use Contao\StringUtil;
use Kiwi\Contao\Blueprints\Model\BlueprintArticleCategoryModel;
use Kiwi\Contao\Blueprints\Model\BlueprintArticleModel;
use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;

class Article
{
    /*
     * Initialize Pasting Mode for Blueprints
     * */
    #[AsCallback(table: 'tl_article', target: 'config.onload')]
    public function initPasting()
    {
        if (Input::get('key') == 'blueprintinsert') {
            $objSession = System::getContainer()->get('request_stack')->getSession();
            $arrClipboard = $objSession->get('CLIPBOARD');

            $arrClipboard['tl_article'] = [
                'id' => 0,
                'mode' => Input::get('mode')
            ];

            $objSession->set('CLIPBOARD', $arrClipboard);
        }
    }

    /*
     * Alter Pasting Button
    */
    //#[AsCallback(table: 'tl_article', target: 'list.sorting.paste_button')]
    public function blueprintArticlePasteButton(\Contao\DataContainer $objDc, array $arrData, string|null $strTable, bool $isCircular, array $arrClipboard, array|null $arrChildren, string|null $strPrev, string|null $strNext)
    {
        $objBlueprintArticleCategoryCollection = BlueprintArticleCategoryModel::findBy('published', 1, ['order' => 'sorting']);

        // Add Child entries with Blueprints
        foreach ($objBlueprintArticleCategoryCollection as $objBlueprintArticleCategory) {
            $objBlueprintArticleCollection = BlueprintArticleModel::findPublishedByPidAndTable($objBlueprintArticleCategory->id, ['order' => 'sorting']);

            if (!$objBlueprintArticleCollection) {
                $objBlueprintArticleCategory->blueprints = [];
                continue;
            }

            $objBlueprintArticleCategory->blueprints = $objBlueprintArticleCollection;
        }

        $href = Backend::addToUrl('');

        return System::getContainer()->get('twig')->render('@Contao/backend/blueprintinsert.html.twig', [
            'categories' => $objBlueprintArticleCategoryCollection,
            'record' => $arrData,
            'href' => $href,
            'icon' => $strTable == 'tl_article' ? "bundles/kiwiblueprints/pasteinto.svg" : "bundles/kiwiblueprints/pastenextto.svg",
            'table' => $strTable,
            'mode' => $strTable == 'tl_article' ? 1 : 2
        ]);
    }

    /*
     * Save Blueprint Category Alias
     * */
    //#[AsCallback(table: 'tl_article', target: 'fields.alias.save')]
    public function generateAlias($varValue, DataContainer $dc)
    {
        $aliasExists = static function (string $alias) use ($dc): bool {
            return Database::getInstance()->prepare("SELECT id FROM tl_blueprint_article_category WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate an alias if there is none
        if (!$varValue) {
            $varValue = System::getContainer()->get('contao.slug')->generate((string)$dc->activeRecord->title, [], $aliasExists);
        } elseif (preg_match('/^[1-9]\d*$/', $varValue)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        } elseif ($aliasExists($varValue)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }
}