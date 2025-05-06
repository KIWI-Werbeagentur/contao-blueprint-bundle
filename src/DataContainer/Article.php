<?php

namespace Kiwi\Contao\BlueprintsBundle\DataContainer;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\Image;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleCategoryModel;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleModel;
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
        $objSession = System::getContainer()->get('request_stack')->getSession();
        $arrClipboard = $objSession->get('CLIPBOARD');

        if (Input::get('key') == 'blueprint_article_insert' || ($arrClipboard['tl_article']['type'] ?? false) == 'blueprint') {
            // paste button
            $objSession = System::getContainer()->get('request_stack')->getSession();
            $arrClipboard = $objSession->get('CLIPBOARD');

            $arrClipboard['tl_article'] = [
                'id' => 0,
                'type' => 'blueprint',
                'mode' => 'create'
            ];

            $objSession->set('CLIPBOARD', $arrClipboard);

            // preview
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/kiwiblueprints/blueprint_insert.js';
            $objLayoutCollection = LayoutModel::findAll();
            $arrIFrames = [];
            foreach ($objLayoutCollection as $objLayout) {
                $objIFrame = new \stdClass();
                $objIFrame->url = "/preview.php/kiwi/blueprints/article?do=blueprint_article&key=blueprint_article_preview&layout={$objLayout->id}";
                $objIFrame->layout = $objLayout->id;
                $arrIFrames[] = json_encode($objIFrame);
            }
            echo "<script>var arrBlueprintPreviewSrcSet = [" . implode(",", $arrIFrames) . "];</script>";
            $GLOBALS['TL_DCA']['tl_article']['list']['sorting']['paste_button_callback'] = [Article::class, 'addBlueprintArticlePasteButton'];
        }
    }

    /*
     * Alter Pasting Button
    */
    public function addBlueprintArticlePasteButton(\Contao\DataContainer $objDc, array $arrData, string|null $strTable, bool $isCircular, array $arrClipboard, array|null $arrChildren, string|null $strPrev, string|null $strNext)
    {
        $security = System::getContainer()->get('security.helper');
        if ($strTable != 'tl_article' && !$security->isGranted(ContaoCorePermissions::DC_PREFIX . 'tl_article', new CreateAction('tl_article', ['pid' => $arrData['id'], 'sorting' => $arrData['sorting']]))) {
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
            'layout' => PageModel::findById($arrData['pid'])->loadDetails()->layout,
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
    public function generateAlias($varValue, DataContainer $objDca)
    {
        $aliasExists = static function (string $alias) use ($objDca): bool {
            return Database::getInstance()->prepare("SELECT id FROM tl_blueprint_article_category WHERE alias=? AND id!=?")->execute($alias, $objDca->id)->numRows > 0;
        };

        // Generate an alias if there is none
        if (!$varValue) {
            $varValue = System::getContainer()->get('contao.slug')->generate((string)$objDca->activeRecord->title, [], $aliasExists);
        } elseif (preg_match('/^[1-9]\d*$/', $varValue)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        } elseif ($aliasExists($varValue)) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }
}