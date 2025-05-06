<?php

namespace Kiwi\Contao\BlueprintsBundle\Drivers;

use Contao\ArticleModel;
use Contao\System;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleModel;
use Contao\DC_Table;
use Contao\Input;

class DC_Table_Blueprint extends DC_Table
{
    /*
     * Set Blueprint as current record, when blueprints are inserted
     * */
    public function getCurrentRecord(int|string|null $id = null, string|null $table = null): array|null
    {
        if (Input::get('key') == 'blueprint_article_insert') {
            return (BlueprintArticleModel::findById(Input::get('id')))->row();
        }
        elseif (Input::get('key') == 'article_insert') {
            $objSession = System::getContainer()->get('request_stack')->getSession();
            $arrClipboard = $objSession->get('CLIPBOARD');
            return (ArticleModel::findById($arrClipboard['tl_article']['id']))->row();
        }
        return parent::getCurrentRecord($id, $table);
    }

    /*
     * Change default $table value to find original record
     * */
    protected function copyChildren($table, $insertID, $id, $parentId): void
    {
        //BUG: Copying children of tl_content
        if (Input::get('key') == 'blueprint_article_insert') {
            $table = ($table == 'tl_content') ? 'tl_content' : 'tl_blueprint_article';
        }
        elseif (Input::get('key') == 'article_insert') {
            $objSession = System::getContainer()->get('request_stack')->getSession();
            $arrClipboard = $objSession->get('CLIPBOARD');
            $id = ($table == 'tl_content') ? $id : $arrClipboard['tl_article']['id'];
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
    public function copyArticle($blnDoNotRedirect = false): void
    {
        $intId = parent::copy($blnDoNotRedirect);
        $this->redirect(self::switchToEdit($intId) . "&do=blueprint_article");
    }
}