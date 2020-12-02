<?php

namespace Ferme\CliController\Actions;

class Upgrade extends Action
{
    public const DESCRIPTION = "Upgrade wiki to current package.";

    public function execute()
    {
        $nbParameters = count($this->parameters);
        if ($nbParameters === 0 or $nbParameters > 1) {
            $this->usage();
            return;
        }

        $this->ferme->wikis->load();
        if ($this->parameters[0] === '--all') {
            $this->upgradeAllWikis();
            return;
        }

        $wikiName = $this->parameters[0];
        if (!isset($this->ferme->wikis[$wikiName])) {
            $this->wikiDoNotExist($wikiName);
            return;
        }

        $this->upgradeOneWiki($wikiName);
    }

    private function upgradeOneWiki($wikiName)
    {
        printf("backup $wikiName : ");
        try {
            $this->ferme->wikis[$wikiName]->archive();
        } catch (\Exception $e) {
            printf("Error : $e->getMessage()\n");
            return;
        }
        printf("OK\n");

        print("Upgrade $wikiName : ");
        try {
            $this->ferme->wikis[$wikiName]->upgrade($this->ferme->getWikiUpgradeSourcePath());
        } catch (\Exception $e) {
            $error = $e->getMessage();
            printf("Error : $error\n");
            return;
        }
        printf("OK\n");
    }

    private function upgradeAllWikis()
    {
        foreach ($this->ferme->wikis as $wikiName => $notUse) {
            $this->upgradeOneWiki($wikiName);
        }
    }

    private function wikiDoNotExist(string $wikiName)
    {
        print("\n$wikiName does not exist.\n\n");
    }

    public function usage()
    {
        print("Usage : \n"
            . "  upgrade wiki_name \tto upgrade one wiki\n"
            . "  upgrade --all \tto upgrade all wiki\n\n"
            . "Before the update, a backup is made.\n"
        );
    }
}
