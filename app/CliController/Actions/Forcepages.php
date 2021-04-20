<?php

namespace Ferme\CliController\Actions;

use Ferme\Wiki\Page;
use Ferme\Wiki\Wiki;
use RecursiveDirectoryIterator;
use Exception;

class Forcepages extends Action
{
    public const DESCRIPTION = "Force in wiki the content of the pages in the \"pages\" folder";

    public function execute()
    {
        $nbParameters = count($this->parameters);
        if ($nbParameters === 0 or $nbParameters > 1) {
            $this->usage();
            return;
        }

        $this->ferme->wikis->load();
        $pages = $this->getPagesToForce();

        if ($this->parameters[0] === '--all') {
            $this->forcePageForAllWikis($pages);
            return;
        }

        $wikiName = $this->parameters[0];
        if (!isset($this->ferme->wikis[$wikiName])) {
            $this->wikiDoNotExist($wikiName);
            return;
        }

        $this->ForcePagesForOneWiki($this->ferme->wikis[$wikiName], $pages);
    }

    private function getPagesToForce(): array
    {
        $pages = array();

        $iterator = new RecursiveDirectoryIterator("pages/", RecursiveDirectoryIterator::SKIP_DOTS);
        foreach ($iterator as $file) {
            $pageName = pathinfo($file->getPathname(), PATHINFO_FILENAME);
            $pages[$pageName] = new Page($pageName);
            $pages[$pageName]->loadFromFile($file->getPathname());
        }

        return $pages;
    }

    private function forcePagesForOneWiki(Wiki $wiki, array $pages)
    {
        printf("Force page to {$wiki->name} : ");
        try {
            foreach ($pages as $page) {
                $page->saveToWiki($wiki);
            }
        } catch (Exception $e) {
            printf("Error : {$e->getMessage()}\n");
            return;
        }
        printf("OK\n");
    }

    private function forcePageForAllWikis(array $pages)
    {
        foreach ($this->ferme->wikis as $wiki) {
            $this->ForcePagesForOneWiki($wiki, $pages);
        }
    }

    private function wikiDoNotExist(string $wikiName)
    {
        print("\n{$wikiName} does not exist.\n\n");
    }

    public function usage()
    {
        print("Usage : \n"
            . "  upgrade wiki_name \tto force pages for one wiki\n"
            . "  upgrade --all \tto force pages for all wiki\n\n"
        );
    }
}
