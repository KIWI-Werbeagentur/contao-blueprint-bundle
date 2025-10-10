<?php

namespace Kiwi\Contao\BlueprintsBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;

class BlueprintArticle
{
    #[AsCallback(table: 'tl_blueprint_article', target: 'fields.alias.save')]
    public function generateAlias($varValue, DataContainer $dc)
    {
        $aliasExists = static function (string $alias) use ($dc): bool {
            if (in_array($alias, array('top', 'wrapper', 'header', 'container', 'main', 'left', 'right', 'footer'), true))
            {
                return true;
            }

            return Database::getInstance()->prepare("SELECT id FROM tl_blueprint_article WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate an alias if there is none
        if (!$varValue)
        {
            $varValue = System::getContainer()->get('contao.slug')->generate((string) $dc->activeRecord->title, (int) $dc->activeRecord->pid, $aliasExists);
        }
        elseif (preg_match('/^[1-9]\d*$/', $varValue))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        }
        elseif ($aliasExists($varValue))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }
}