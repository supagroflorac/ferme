<?php

namespace Ferme\CliController\Actions;

use Ferme\Wiki\WikiFactory;

class Mv extends Action
{
    public const DESCRIPTION = "Rename a wiki";

    public function execute()
    {
        $nbParameters = count($this->parameters);
        if ($nbParameters !== 2) {
            $this->usage();
            return;
        }
        
        $wikiToRename = $this->parameters[0];
        $newWikiName = $this->parameters[1];

        $this->ferme->wikis->load();

        if (!isset($this->ferme->wikis[$wikiToRename])) {
            print("\n{$wikiToRename} does not exist.\n\n");
            return;
        }

        if (isset($this->ferme->wikis[$newWikiName])) {
            print("\n{$newWikiName} already exist.\n\n");
            return;
        }

        $wikiFactory = new WikiFactory(
            $this->ferme->config,
            $this->ferme->dbConnexion
        );

        try {
            $newWiki = $wikiFactory->copyWiki(
                $this->ferme->wikis[$wikiToRename],
                $newWikiName
            );
            $this->ferme->wikis->add($newWikiName, $newWiki);
        } catch (Exception $e) {
            printf("Error durant la copie de {$wikiToRename} vers {$newWikiName} : {$e->getMessage()}\n");
            return;
        }

        try {
            $this->ferme->delete($wikiToRename);
        } catch (Exception $e) {
            printf("Error durant la suppression de {$wikiToRename} : {$e->getMessage()}\n");
            return;
        }
    }

    public function usage()
    {
        print("Usage : \n"
            . " mv wiki new_wiki \t to copy a wiki\n"
        );
    }
}
