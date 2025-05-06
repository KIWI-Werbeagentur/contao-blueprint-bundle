<?php

namespace Kiwi\Contao\BlueprintsBundle\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;

class Content
{
    /*
     * Change contents ptable to "tl_article" when copying blueprint
     * */
    #[AsCallback(table: 'tl_content', target: 'config.oncopy')]
    public function onCopyListener($intID, DataContainer $objDca)
    {
        $objSession = System::getContainer()->get('request_stack')->getSession();
        $arrClipboard = $objSession->get('CLIPBOARD');

        if (Input::get('key') == 'blueprint_article_insert' || ($arrClipboard['tl_article']['type'] ?? false) == 'blueprint') {
            $objContent = ContentModel::findById($intID);
            $objContent->ptable = ($objContent->ptable == "tl_blueprint_article") ? "tl_article" : $objContent->ptable;
            $objContent->save();
        }

        if (Input::get('key') == 'article_insert' || ($arrClipboard['tl_article']['type'] ?? false) == 'article') {
            $objContent = ContentModel::findById($intID);
            $objContent->ptable = "tl_blueprint_article";
            $objContent->save();
        }
    }
}