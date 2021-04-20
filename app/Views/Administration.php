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
        foreach ($listWiki as $wiki) {
            $wiki->infos['LasPageModificationDateTime'] = $wiki->getLasPageModificationDateTime();
            $wiki->infos['FilesDiskUsage'] = $wiki->getDiskUsage();
            $wiki->infos['Release'] = $wiki->getRelease();
            $wiki->infos['Version'] = $wiki->getVersion();
        }
        $infos['list_wikis'] = $this->object2Infos($listWiki);

        $infos['list_archives'] =
            $this->object2Infos($this->ferme->archives->search());

        return $infos;
    }
}
