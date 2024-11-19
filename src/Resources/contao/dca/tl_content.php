<?php

$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = [\Kiwi\Contao\Blueprints\DataContainer\Content::class, 'onCopyListener'];