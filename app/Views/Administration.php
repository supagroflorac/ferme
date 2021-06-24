<?php

namespace Ferme\Views;

class Administration extends TwigView
{
    protected function compileInfos(): array
    {
        $infos = array();
        $infos['list_wikis'] = $this->extractWikisInfos();
        $infos['list_archives'] = $this->extractArchivesInfos();
        return $infos;
    }

    private function extractWikisInfos(): array
    {
        $listInfos = array();
        
        foreach ($this->ferme->wikis->search() as $wiki) {
            $listInfos[$wiki->name] = array(
                'name' => $wiki->name,
                'url' => $wiki->config['base_url'],
                'date' => $wiki->infos['date'],
                'mail' => $wiki->infos['mail'],
                'Release' => $wiki->getRelease(),
                'Version' => $wiki->getVersion(),
                'FilesDiskUsage' => $wiki->getDiskUsage(),
            );
        }
        return $listInfos;
    }

    private function extractArchivesInfos(): array
    {
        $listInfos = array();
        foreach ($this->ferme->archives->search() as $archive) {
            $listInfos[$archive->name] = array(
                'name' => $archive->name,
                'filename' => $archive->filename,
                'date' => $archive->creationDate,
                'url' => $archive->url,
                'size' => $archive->size(),
            );
        }
        return $listInfos;

    }
}
