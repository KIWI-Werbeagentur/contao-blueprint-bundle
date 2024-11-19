<?php

namespace Kiwi\Contao\Blueprints\DataContainer;

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
     * Create Blueprint Insert Button at Article
     * */
    #[AsCallback(table: 'tl_article', target: 'list:operations.blueprint.button')]
    public function blueprintArticleOpButton(array $arrData, string|null $strHref, string|null $strLabel, string|null $strTitle, string|null $strIcon, string|null $strAttributes, string|null $strTable, array $arrRootIds, array|null $arrChildIds, bool $isRef, string|null $strPrev, string|null $strNext, \Contao\DataContainer $objDc)
    {
        if (Input::get('key') == 'blueprintinsert') {
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

            $v = $GLOBALS['TL_DCA'][$strTable]['list']['operations']['blueprint'];

            if (!empty($v['route']))
            {
                $href = System::getContainer()->get('router')->generate($v['route']);
            }
            else
            {
                $href = Backend::addToUrl($v['href'] ?? '');
            }

            return System::getContainer()->get('twig')->render('@Contao/backend/blueprintinsert.html.twig', [
                'categories' => $objBlueprintArticleCategoryCollection,
                'record' => $arrData,
                'href' => $href,
                'label' => $strLabel,
                'title' => $strTitle,
                'icon' => $strIcon,
                'attributes' => $strAttributes,
                'table' => $strTable,
            ]);
        }
        return "";
    }

    /*
     * Save Blueprint Category Alias
     * */
    #[AsCallback(table: 'tl_article', target: 'fields.alias.save')]
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