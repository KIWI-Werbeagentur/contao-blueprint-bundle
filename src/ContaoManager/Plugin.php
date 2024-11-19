<?php

namespace Kiwi\Contao\Blueprints\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Kiwi\Contao\BackgroundImageBundle\KiwiBackgroundImageBundle;
use Kiwi\Contao\Blueprints\KiwiBlueprints;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(KiwiBlueprints::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class
                ]),
        ];
    }
}
