<?php

namespace Ferme\Views;

class Home extends TwigView
{
    protected function compileInfos(): array
    {
        return array(
            'wiki_name' => $this->getInputPostVarOrEmpty('wiki_name'),
            'description' => $this->getInputPostVarOrEmpty('description'),
            'mail' => $this->getInputPostVarOrEmpty('mail'),
            'list_wikis' => $this->object2Infos($this->ferme->wikis->search()),
            'hashcash_url' => $this->HashCash(),
            'welcome_text' => $this->ferme->config['welcome_text'],
        );
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
