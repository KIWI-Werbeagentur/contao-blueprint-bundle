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

        $security = System::getContainer()->get('security.helper');

        $id = $arrData['id'];

        $labelPasteAfter = $GLOBALS['TL_LANG'][$strTable]['pasteafter'] ?? $GLOBALS['TL_LANG']['DCA']['pasteafter'];
        $imagePasteAfter = Image::getHtml('pasteafter.svg', \sprintf($labelPasteAfter[1], $id));

        $labelPasteInto = $GLOBALS['TL_LANG'][$strTable]['pasteinto'] ?? $GLOBALS['TL_LANG']['DCA']['pasteinto'];
        $imagePasteInto = Image::getHtml('pasteinto.svg', \sprintf($labelPasteInto[1], $id));

        if ($strTable == "tl_article") {
            if (($arrClipboard['mode'] == 'cut' && ($isCircular || $arrClipboard['id'] == $id)) || ($arrClipboard['mode'] == 'cutAll' && ($isCircular || \in_array($id, $arrClipboard['id']))) || !$this->canPasteClipboard($arrClipboard, ['pid' => $currentRecord['pid'], 'sorting' => $currentRecord['sorting'] + 1])) {
                return Image::getHtml('pasteafter--disabled.svg') . ' ';
            } else {
                return '<a href="' . Backend::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $id . (!\is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars(\sprintf($labelPasteAfter[1], $id)) . '" data-action="contao--scroll-offset#store">' . $imagePasteAfter . '</a> ';
            }
        } else {
            if (!$security->isGranted(ContaoCorePermissions::DC_PREFIX . 'tl_article', new CreateAction('tl_article', ['pid' => $arrData['id'], 'sorting' => $arrData['sorting']]))) {
                return Image::getHtml('pasteinto--disabled.svg') . ' ';
            } else {
                System::loadLanguageFile('tl_article');
                System::loadLanguageFile('default');
                $labelPasteInto = $GLOBALS['TL_LANG']['tl_article']['pasteinto'] ?? $GLOBALS['TL_LANG']['DCA']['pasteinto'];
                $imagePasteInto = Image::getHtml('pasteinto.svg', $labelPasteInto[0] ?? "");
                return '<a href="' . Backend::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=0' . (!\is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars($labelPasteInto[0] ?? "") . '" data-action="contao--scroll-offset#store">' . $imagePasteInto . '</a> ';
            }
        }
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