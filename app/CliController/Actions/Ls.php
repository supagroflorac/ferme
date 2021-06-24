<?php

namespace Ferme\CliController\Actions;

class Ls extends Action
{
    public const DESCRIPTION = "List wikis.";

    public function execute()
    {
        $listWiki = $this->ferme->wikis->load();
        $listWiki = $this->ferme->wikis->search();
        print("\n");
        foreach ($listWiki as $wiki) {
            $name = str_pad($wiki->name, 11);
            $mail = str_pad($wiki->infos['mail'], 20);
            $creationDate = date('Y-m-d', $wiki->infos['date']);
            $diskUsage = number_format($wiki->getDiskUsage() / 1024 / 1024, 2);
            $version = str_pad($wiki->getVersion() . ':' . $wiki->getRelease(), 25);

            print("$name $mail $creationDate\t${version}\t${diskUsage}Mo\n");
        }
        print("\n");
    }

    public function usage()
    {
        printf("No parameters.\n");
    }
}
