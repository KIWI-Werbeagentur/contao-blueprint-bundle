<?php

namespace Kiwi\Contao\Blueprints\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * @property int    $tstamp
 * @property string $title
 * @method static BlueprintArticleCategoryModel|Collection|BlueprintArticleCategoryModel[]|null findAll(array $options = [])
 */
class BlueprintArticleCategoryModel extends Model
{
    protected static $strTable = 'tl_blueprint_article_category';
}
