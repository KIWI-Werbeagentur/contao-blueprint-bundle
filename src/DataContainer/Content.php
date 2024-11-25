<?php

namespace Kiwi\Contao\Blueprints\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;

class Content
{
    /*
     * Change contents ptable to "tl_article" when copying blueprint
     * */
    #[AsCallback(table: 'tl_content', target: 'config.oncopy')]
    public function onCopyListener($intID, DataContainer $dc)
    {
        if (Input::get('key') == 'blueprint_article_insert') {
            $objContent = ContentModel::findById($intID);
            $objContent->ptable = "tl_article";
            $objContent->save();
        }
    }
}