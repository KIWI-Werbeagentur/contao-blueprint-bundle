<?php

namespace Kiwi\Contao\Blueprints;

use Kiwi\Contao\Blueprints\Drivers\DC_Table_Blueprint;
use Kiwi\Contao\Blueprints\Model\BlueprintArticleModel;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Input;
use Contao\PageModel;
use Contao\PageRegular;

class Blueprint
{
    /*
     * Create pseudo-page when in Blueprint-Preview mode
     * */
    public function preview()
    {
        global $objPage;
        $objPage = new PageModel();
        $objPage->id = 0;
        $objPage->type = 'preview';
        $objPage->title = "Blueprint Preview";
        $objPage->alias = "Preview";
        $objPage->layout = Input::get('layout');
        $objPage->includeLayout = Input::get('layout');
        $objPage->layoutId = Input::get('layout');
        $objPage->language = $GLOBALS['TL_LANGUAGE'];
        $objPage->noSearch = true;
        $objPage->protected = false;

        throw new ResponseException((new PageRegular())->getResponse($objPage));
    }

    /*
     * Copy chosen blueprint article into tl_article
     * */
    public function insert(): void
    {
        $intBlueprint = Input::get('id');
        $objBlueprint = BlueprintArticleModel::findById($intBlueprint);

        $objBlueprint->pid = Input::get('pid');
        (new DC_Table_Blueprint('tl_article', $objBlueprint->row()))->copy(true);
    }
}