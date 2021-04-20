<?php

namespace Ferme\Views;

use Ferme\Views\TwigView;

class AjaxListWikis extends TwigView
{
    private $filter = "*";

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    protected function compileInfos(): array
    {
        $infos = array();

        $infos['list_wikis'] = $this->object2Infos(
            $this->ferme->wikis->searchNoCaseType($this->filter)
        );

        return $infos;
    }
}
