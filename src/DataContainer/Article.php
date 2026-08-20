<?php

namespace Kiwi\Contao\BlueprintsBundle\DataContainer;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
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
    #[AsCallback(table: 'tl_article', target: 'config.oncopy')]
    public function onCopyListener($intID, DataContainer $objDca)
    {
        $objArticle = ArticleModel::findByPk($intID);

        if(!$objArticle) return;

        $objArticle->template = 0;
        $objArticle->save();
    }

    /*
     * Initialize Pasting Mode for Blueprints
     * Add Preview
     * */
    public function initPasting()
    {
        $objSession = System::getContainer()->get('request_stack')->getSession();
        $arrClipboard = $objSession->get('CLIPBOARD');

        // Load preview JavaScript (required for Turbo navigation)
        echo "<script>var strBlueprintPreview = '/kiwi/blueprints/article';</script>";
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/kiwiblueprints/blueprint_insert.js|static';

        if (Input::get('key') == 'blueprint_article_insert' || ($arrClipboard['tl_article']['type'] ?? false) == 'blueprint') {
            $arrClipboard['tl_article'] = [
                'id' => 0,
                'type' => 'blueprint',
                'mode' => 'create'
            ];

            $objSession->set('CLIPBOARD', $arrClipboard);

            $GLOBALS['TL_DCA']['tl_article']['list']['sorting']['paste_button_callback'] = [Article::class, 'addBlueprintArticlePasteButton'];
        }
    }

    /*
     * Alter Pasting Button
    */
    public function addBlueprintArticlePasteButton(\Contao\DataContainer $objDc, array $arrData, string|null $strTable, bool $isCircular, array $arrClipboard, array|null $arrChildren, string|null $strPrev, string|null $strNext)
    {
        $security = System::getContainer()->get('security.helper');
        if ($strTable != 'tl_article' && $strTable != 'tl_page' && !$security->isGranted(ContaoCorePermissions::DC_PREFIX . 'tl_article', new CreateAction('tl_article', ['pid' => $arrData['id'], 'sorting' => $arrData['sorting']]))) {
            return;
        }

        $objBlueprintArticleCategoryCollection = BlueprintArticleCategoryModel::findBy('published', 1, ['order' => 'sorting']);

        if (null === $objBlueprintArticleCategoryCollection) {
            return '';
        }

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

        $pageId = null;
        $articleId = null;

        if ($strTable == 'tl_page') {
            $pageId = $arrData['id'];
        } elseif ($strTable == 'tl_article') {
            $pageId = $arrData['pid'];
            $articleId = $arrData['id'];
        } elseif ($strTable == 'tl_content') {
            $articleId = $arrData['pid'];
            $objArticle = ArticleModel::findById($articleId);
            if ($objArticle) {
                $pageId = $objArticle->pid;
            }
        }

        $pageUrl = '/';
        $objPage = $pageId ? PageModel::findById($pageId) : null;

        if ($objPage) {
            try {
                $pageUrl = $objPage->getAbsoluteUrl();
            } catch (\Throwable $e) {
                $pageUrl = '/' . ($objPage->alias ?: $pageId) . '/';
            }
        }

        $intLayout = null !== $objPage ? $objPage->loadDetails()->layout : 0;

        $position = 0;
        if ($pageId) {
            $articles = ArticleModel::findPublishedByPidAndColumn($pageId, 'main');
            if ($articles) {
                $pos = 0;
                foreach ($articles as $article) {
                    $pos++;
                    if ($article->id == $articleId) {
                        $position = $pos;
                        break;
                    }
                }
            }
        }

        return System::getContainer()->get('twig')->render('@KiwiBlueprints/backend/blueprint_article_insert.html.twig', [
            'categories' => $objBlueprintArticleCategoryCollection,
            'record' => $arrData,
            'layout' => $intLayout,
            'page' => $pageId,
            'pageUrl' => $pageUrl,
            'position' => $position,
            'afterArticle' => $articleId,
            'href' => $href,
            'icon' => $strTable == 'tl_content' ? "bundles/kiwiblueprints/pastenextto.svg" : "bundles/kiwiblueprints/pasteinto.svg",
            'table' => $strTable,
            'mode' => $strTable == 'tl_content' ? 2 : 1
        ]);
    }

    /*
     * Save Blueprint Category Alias
     * */
    #[AsCallback(table: 'tl_article', target: 'fields.alias.save')]
    public function generateAlias($varValue, DataContainer $objDca)
    {
        $aliasExists = static function (string $alias) use ($objDca): bool {
            return Database::getInstance()->prepare("SELECT id FROM tl_article WHERE alias=? AND id!=?")->execute($alias, $objDca->id)->numRows > 0;
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
