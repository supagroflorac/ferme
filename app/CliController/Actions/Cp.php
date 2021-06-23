<?php

namespace Ferme\CliController\Actions;

use Ferme\Wiki\WikiFactory;

class Cp extends Action
{
    public const DESCRIPTION = "List wikis.";

    public function execute()
    {
        $nbParameters = count($this->parameters);
        if ($nbParameters !== 2) {
            $this->usage();
            return;
        }
        
        $wikiToCopyName = $this->parameters[0];
        $newWikiName = $this->parameters[1];

        $this->ferme->wikis->load();

        if (!isset($this->ferme->wikis[$wikiToCopyName])) {
            print("\n{$wikiToCopyName} does not exist.\n\n");
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
                $this->ferme->wikis[$wikiToCopyName],
                $newWikiName
            );
            $this->ferme->wikis->add($newWikiName, $newWiki);
        } catch (Exception $e) {
            $error = $e->getMessage();
            printf("Error : $error\n");
            return;
        }
    }

    public function usage()
    {
        print("Usage : \n"
            . " cp wiki new_wiki \t to copy a wiki\n"
        );
    }
}
