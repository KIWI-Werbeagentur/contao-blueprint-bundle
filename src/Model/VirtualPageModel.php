<?php

namespace Kiwi\Contao\Blueprints\Model;

use Contao\PageModel;

//Enables you to initialize a non-existent page without throwing errors because of a missing root page
class VirtualPageModel extends PageModel{
    public function loadDetails()
    {

    }
}