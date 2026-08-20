<?php

namespace Kiwi\Contao\BlueprintsBundle;

use Contao\ArticleModel;
use Contao\System;
use Kiwi\Contao\BlueprintsBundle\Drivers\DC_Table_Blueprint;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleModel;
use Contao\Input;

class Blueprint
{
    /*
     * Copy chosen blueprint article into tl_article
     * */
    public function insertBlueprint(): void
    {
        $intBlueprint = Input::get('id');
        $objBlueprint = BlueprintArticleModel::findById($intBlueprint);

        if (!$objBlueprint) {
            throw new \RuntimeException(sprintf('Blueprint article with ID %s not found.', $intBlueprint));
        }

        $objBlueprint->pid = Input::get('pid');
        (new DC_Table_Blueprint('tl_article', $objBlueprint->row()))->copyBlueprint(true);
    }

    public function insertArticle($id, $blnRedirect = false){
        $objArticle = ArticleModel::findByPk($id);

        if (!$objArticle) {
            throw new \RuntimeException(sprintf('Article with ID %s not found.', $id));
        }

        $objArticle->pid = intval(Input::get('pid'));
        return (new DC_Table_Blueprint('tl_blueprint_article', $objArticle->row()))->copyArticle($id, $blnRedirect);
    }

    public function insertArticles():void
    {
        $objSession = System::getContainer()->get('request_stack')->getSession();
        $arrClipboard = $objSession->get('CLIPBOARD');

        if($arrClipboard['tl_article'] ?? false){
            if(!is_array($arrClipboard['tl_article']['id'])){
                $objDc = $this->insertArticle($arrClipboard['tl_article']['id'], true);
            }
            else{
                $arrArticles = $arrClipboard['tl_article']['id'];
                $lastId = null;
                foreach ($arrArticles as $id) {
                    $arrClipboard['tl_article']['id'] = $id;
                    $objSession->set('CLIPBOARD', $arrClipboard);
                    $objDc = $this->insertArticle($id);
                    $lastId = $id;
                }
                $objSession->set('CLIPBOARD', []);
                $objDc->redirect($objDc::getReferer($lastId) . "&do=blueprint_article");
            }
        }
    }
}
