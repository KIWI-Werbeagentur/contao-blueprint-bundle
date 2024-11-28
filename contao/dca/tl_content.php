<?php

$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = [\Kiwi\ContaoBlueprintsBundle\DataContainer\Content::class, 'onCopyListener'];