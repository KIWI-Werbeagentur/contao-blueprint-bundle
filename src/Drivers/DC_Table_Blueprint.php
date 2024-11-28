<?php

namespace Kiwi\ContaoBlueprintsBundle\Drivers;

use Kiwi\ContaoBlueprintsBundle\Model\BlueprintArticleModel;
use Contao\DC_Table;
use Contao\Input;

class DC_Table_Blueprint extends DC_Table
{
    /*
     * Set Blueprint as current record, when blueprints are inserted
     * */
    public function getCurrentRecord(int|string|null $id = null, string|null $table = null): array|null
    {
        return (BlueprintArticleModel::findById(Input::get('id')))->row();
    }

    /*
     * Change default $table value to find original record
     * */
    protected function copyChildren($table, $insertID, $id, $parentId): void
    {
        parent::copyChildren('tl_blueprint_article', $insertID, $id, $parentId);
    }

    /*
     * implement custom redirection after copying, to get to new article instead of blueprint_article
     * */
    public function copy($blnDoNotRedirect = false): void
    {
        $intId = parent::copy($blnDoNotRedirect);
        $this->redirect(self::switchToEdit($intId) . "&do=article");
    }
}