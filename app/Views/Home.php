<?php

namespace Ferme\Views;

class Home extends TwigView
{
    protected function compileInfos(): array
    {
        $infos = array(
            'wiki_name' => $this->getInputPostVarOrEmpty('wiki_name'),
            'description' => $this->getInputPostVarOrEmpty('description'),
            'mail' => $this->getInputPostVarOrEmpty('mail'),
            'hashcash_url' => $this->HashCash(),
            'welcome_text' => $this->ferme->config['welcome_text'],
        );

        $infos['list_wikis'] = $this->extractWikisInfo();

        return $infos;
    }
    
    private function extractWikisInfo(): array
    {
        $listInfos = array();
        
        foreach ($this->ferme->wikis->search() as $wiki) {
            $listInfos[$wiki->name] = array(
                'name' => $wiki->name,
                'url' => $wiki->config['base_url'],
                'date' => $wiki->infos['date'],
                'description' => html_entity_decode($wiki->infos['description'], ENT_QUOTES, "UTF-8"),
            );
        }
        return $listInfos;
    }

    private function getInputPostVarOrEmpty(string $varname): string
    {
        if (filter_has_var(INPUT_POST, $varname)) {
            return filter_input(INPUT_POST, $varname, FILTER_SANITIZE_STRING);
        }
        return "";
    }

    private function hashCash(): string
    {
        return "app/wp-hashcash-js.php?siteurl={$this->ferme->config['base_url']}";
    }
}
