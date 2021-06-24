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
        $listInfos = array(
            'list_wikis' => array(),
        );
        
        foreach ($this->ferme->wikis->searchNoCaseType($this->filter) as $wiki) {
            $listInfos['list_wikis'][$wiki->name] = array(
                'name' => $wiki->name,
                'url' => $wiki->config['base_url'],
                'date' => $wiki->infos['date'],
                'description' => html_entity_decode($wiki->infos['description'], ENT_QUOTES, "UTF-8"),
            );
        }

        return $listInfos;
    }
}
