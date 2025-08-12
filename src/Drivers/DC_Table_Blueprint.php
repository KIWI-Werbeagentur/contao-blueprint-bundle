<?php

namespace Kiwi\Contao\BlueprintsBundle\Drivers;

use Contao\ArticleModel;
use Contao\Backend;
use Contao\Controller;
use Contao\Database;
use Contao\PageModel;
use Contao\System;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleModel;
use Contao\DC_Table;
use Contao\Input;

class DC_Table_Blueprint extends DC_Table
{
    public function __construct($strTable, $arrModule=array())
    {
        if (Input::get('clipboard') !== null)
        {
            $this->addToUrl('', false, ['key']);
            Input::setGet('key',null);
        }
        parent::__construct($strTable, $arrModule);
    }

    /*
     * Set Blueprint as current record, when blueprints are inserted
     * */
    public function getCurrentRecord(int|string|null $id = null, string|null $table = null): array|null
    {
        $objSession = System::getContainer()->get('request_stack')->getSession();
        if (!$table && (Input::get('act') == 'copy' || (Input::get('act') == 'paste' && Input::get('original')) || !Input::get('act')) && ($objSession->get('CLIPBOARD')[$this->strTable]['original'] ?? null) == 'tl_blueprint_article') {
            if(($objSession->get('CLIPBOARD')[$this->strTable]['original'] ?? null) == 'tl_blueprint_article') {
                $intId = Input::get('id') ?? $objSession->get('CLIPBOARD')[$this->strTable]['id'] ?? null;
                if (!$intId) return null;
                $objBlueprint = (BlueprintArticleModel::findById($intId))->row();
                return $objBlueprint;
            }
        } elseif (Input::get('key') == 'blueprint_article_insert' && Input::get('id') && BlueprintArticleModel::findById(Input::get('id'))) {
            return (BlueprintArticleModel::findById(Input::get('id')))->row();
        } elseif (Input::get('key') == 'article_insert') {
            return (ArticleModel::findById($this->intCurrentRecord))->row();
        }
        return parent::getCurrentRecord($id, $table);
    }

    public function showAll()
    {
        $this->limit = '';

        $objSession = System::getContainer()->get('request_stack')->getSession();

        $this->reviseTable();

        // Add to clipboard
        if (Input::get('act') == 'paste') {
            //$this->denyAccessUnlessGranted(...$this->getClipboardPermission(Input::get('mode'), (int) Input::get('id')));

            $children = Input::get('children');

            // Backwards compatibility
            if (Input::get('childs') !== null) {
                trigger_deprecation('contao/core-bundle', '5.3', 'Using the "childs" query parameter has been deprecated and will no longer work in Contao 6. Use the "children" parameter instead.');
                $children = Input::get('childs');
            }

            $arrClipboard = $objSession->get('CLIPBOARD');

            $arrClipboard[$this->strTable] = [
                'id' => Input::get('id'),
                'childs' => $children, // backwards compatibility
                'children' => $children,
                'original' => Input::get('original') ?? null,
                'mode' => Input::get('mode')
            ];

            $objSession->set('CLIPBOARD', $arrClipboard);
            Input::setGet('original', null);

            if ($this->currentPid) {
                if (Input::get('key') == 'blueprint_article_clipboard') {
                    $this->redirect(Backend::addToUrl('', false, ['act', 'mode', 'original']));
                } else {
                    $this->redirect(Backend::addToUrl('id=' . $this->currentPid, false, ['act', 'mode', 'original']));
                }
            } else {
                $this->redirect(Backend::addToUrl('', false, ['act', 'mode', 'id', 'original']));
            }
        }

        return parent::showAll();
    }

    /*
     * Change default $table value to find original record
     * */
    protected function copyChildren($table, $insertID, $id, $parentId): void
    {
        //BUG: Copying children of tl_content
        if (Input::get('key') == 'blueprint_article_insert') {
            $table = ($table == 'tl_content') ? 'tl_content' : 'tl_blueprint_article';
        } elseif (Input::get('key') == 'article_insert') {
            $id = ($table == 'tl_content') ? $id : $this->intCurrentRecord;
            $table = ($table == 'tl_content') ? 'tl_content' : 'tl_article';
        }
        parent::copyChildren($table, $insertID, intval($id), $parentId);
    }

    /*
     * implement custom redirection after copying, to get to new article instead of blueprint_article
     * */
    public function copyBlueprint($blnDoNotRedirect = false): void
    {
        $intId = parent::copy($blnDoNotRedirect);
        $this->redirect(self::switchToEdit($intId) . "&do=article");
    }

    /*
     * implement custom redirection after copying, to get to new article instead of blueprint_article
     * */
    public function copyArticle($intCurrentRecord, $blnDoNotRedirect = false): void
    {
        $this->intCurrentRecord = $intCurrentRecord;
        $intId = parent::copy(true);
        if (!$blnDoNotRedirect) {
            $this->redirect(self::switchToEdit($intId) . "&do=blueprint_article");
        }
    }
}