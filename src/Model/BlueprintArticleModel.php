<?php

namespace Kiwi\ContaoBlueprintsBundle\Model;

use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

/**
 * @property int $tstamp
 * @property string $title
 * @method static BlueprintArticleCategoryModel|Collection|BlueprintArticleCategoryModel[]|null findAll(array $options = [])
 */
class BlueprintArticleModel extends Model
{
    protected static $strTable = 'tl_blueprint_article';

    public static function findPublishedByPidAndTable($intPid, array $arrOptions = [])
    {
        $t = static::$strTable;
        $arrColumns = ["$t.pid=?"];

        if (!static::isPreviewMode($arrOptions)) {
            $time = Date::floorToMinute();
            $arrColumns[] = "$t.published=1 AND ($t.start='' OR $t.start<=$time) AND ($t.stop='' OR $t.stop>$time)";
        }

        // Skip unsaved elements (see #2708)
        $arrColumns[] = "$t.tstamp!=0";

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.sorting";
        }

        return static::findBy($arrColumns, [$intPid], $arrOptions);
    }
}
