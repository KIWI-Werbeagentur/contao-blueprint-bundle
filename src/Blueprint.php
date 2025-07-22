<?php

namespace Kiwi\Contao\BlueprintsBundle;

use Contao\ArticleModel;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
use Kiwi\Contao\BlueprintsBundle\Drivers\DC_Table_Blueprint;
use Kiwi\Contao\BlueprintsBundle\Model\BlueprintArticleModel;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Input;
use Contao\PageRegular;
use Kiwi\Contao\BlueprintsBundle\Model\VirtualPageModel;

class Blueprint
{
    /*
     * Create pseudo-page when in Blueprint-Preview mode
     * */
    public function preview()
    {
        global $objPage;

        if(Input::get('page')){
            $objPage = PageModel::findByPk(Input::get('page'));
        }
        elseif(Input::get('layout')) {
            $objPage = new VirtualPageModel();
            $objPage->id = 0;
            $objPage->type = 'root';
            $objPage->title = "Blueprint Preview";
            $objPage->alias = "Preview";
            $objPage->layout = Input::get('layout') ?? LayoutModel::findAll()->first()->id;
            $objPage->includeLayout = Input::get('layout');
            $objPage->layoutId = Input::get('layout');
            $objPage->language = $GLOBALS['TL_LANGUAGE'];
            $objPage->noSearch = true;
            $objPage->protected = false;
        }
        $objPage->loadDetails();
        $objPage->isBlueprintPreview = 1;

        $requestStack = System::getContainer()->get('request_stack');
        $currentRequest = $requestStack->getCurrentRequest();
        $currentRequest->attributes->set('pageModel', $objPage);

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/kiwiblueprints/blueprint_preview.js';
        $GLOBALS['TL_CSS'][] = 'bundles/kiwiblueprints/blueprint.css';

        throw new ResponseException((new PageRegular())->getResponse($objPage));
    }

    /*
     * Copy chosen blueprint article into tl_article
     * */
    public function insertBlueprint(): void
    {
        $intBlueprint = Input::get('id');
        $objBlueprint = BlueprintArticleModel::findById($intBlueprint);

        $objBlueprint->pid = Input::get('pid');
        (new DC_Table_Blueprint('tl_article', $objBlueprint->row()))->copyBlueprint(true);
    }

    public function copyArticle($intId, $blnDoNotRedirect = false)
    {
        $objArticle = ArticleModel::findByPk($intId);
        $objArticle->pid = intval(Input::get('pid'));

        $objDCTable = (new DC_Table_Blueprint('tl_blueprint_article', $objArticle->row()));
        $objDCTable->copyArticle($intId, $blnDoNotRedirect);

        return $objDCTable;
    }

    public function insertArticle(): void
    {
        $objSession = System::getContainer()->get('request_stack')->getSession();
        $arrClipboard = $objSession->get('CLIPBOARD');

        if ($arrClipboard['tl_article'] ?? false) {
            if (is_array($arrClipboard['tl_article']['id'])) {
                foreach ($arrClipboard['tl_article']['id'] ?? [] as $intId) {
                    $objDCTable = $this->copyArticle($intId, true);
                }
                $objDCTable::redirect($objDCTable::getReferer());
                return;
            }
            $objDCTable = $this->copyArticle($arrClipboard['tl_article']['id'], false);
        }
    }
}