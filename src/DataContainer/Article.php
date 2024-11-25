<?php

namespace Kiwi\Contao\Blueprints\DataContainer;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\Image;
use Contao\LayoutModel;
use Contao\PageModel;
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
     * Add Preview
     * */
    public function initPasting()
    {
        if (Input::get('key') == 'blueprint_article_insert') {
            // paste button
            $objSession = System::getContainer()->get('request_stack')->getSession();
            $arrClipboard = $objSession->get('CLIPBOARD');

            $arrClipboard['tl_article'] = [
                'id' => 0,
                'mode' => 'create'
            ];

            $objSession->set('CLIPBOARD', $arrClipboard);

            // preview
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/kiwiblueprints/blueprint_insert.js';
            $objLayoutCollection = LayoutModel::findAll();
            $arrIFrames = [];
            foreach ($objLayoutCollection as $objLayout){
                $objIFrame = new \stdClass();
                $objIFrame->url = "/contao?do=blueprint_article&key=blueprint_article_preview&layout={$objLayout->id}";
                $objIFrame->layout = $objLayout->id;
                $arrIFrames[] = json_encode($objIFrame);
            }
            echo "<script>var arrBlueprintPreviewSrcSet = [" . implode(",", $arrIFrames) . "];</script>";
        }
    }

    /*
     * Alter Pasting Button
    */
    public function addBlueprintArticlePasteButton(\Contao\DataContainer $objDc, array $arrData, string|null $strTable, bool $isCircular, array $arrClipboard, array|null $arrChildren, string|null $strPrev, string|null $strNext)
    {
        $security = System::getContainer()->get('security.helper');
        if ($strTable!='tl_article' && !$security->isGranted(ContaoCorePermissions::DC_PREFIX . 'tl_article', new CreateAction('tl_article', ['pid' => $arrData['id'], 'sorting' => $arrData['sorting']]))) {
            return;
        }

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

        return System::getContainer()->get('twig')->render('@KiwiBlueprints/backend/blueprint_article_insert.html.twig', [
            'categories' => $objBlueprintArticleCategoryCollection,
            'record' => $arrData,
            'layout' => $arrData['layout'] ?? PageModel::findById($arrData['pid'])->layout,
            'href' => $href,
            'icon' => $strTable == 'tl_article' ? "bundles/kiwiblueprints/pasteinto.svg" : "bundles/kiwiblueprints/pastenextto.svg",
            'table' => $strTable,
            'mode' => $strTable == 'tl_article' ? 1 : 2
        ]);
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