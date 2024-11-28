<?php

$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = [\Kiwi\Contao\BlueprintsBundle\DataContainer\Content::class, 'onCopyListener'];