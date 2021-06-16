<?php

namespace Ferme\Views;

class Administration extends TwigView
{
    protected function compileInfos(): array
    {
        $infos = array();

        // Ajoute la date de dernière modification et l'espace occupés par le
        // repertoire 'files' aux informations sur le wiki.
        $listWiki = $this->ferme->wikis->search();
        $infos['list_wikis'] = $this->object2Infos($listWiki);

        $infos['list_archives'] =
            $this->object2Infos($this->ferme->archives->search());

        return $infos;
    }
}
