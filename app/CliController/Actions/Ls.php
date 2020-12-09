<?php

namespace Ferme\CliController\Actions;

/**
 * @author Florestan Bredow <florestan.bredow@supagro.fr>
 * @link http://www.phpdoc.org/docs/latest/index.html
 */
class Ls extends Action
{
    public const DESCRIPTION = "List wikis.";

    public function execute()
    {
        $listWiki = $this->ferme->wikis->load();
        $listWiki = $this->ferme->wikis->search();
        print("\n");
        foreach ($listWiki as $wiki) {
            $infos = $wiki->getInfos();
            $name = str_pad($infos['name'], 11);
            $mail = str_pad($infos['mail'], 20);
            $creationDate = date('Y-m-d', $infos['date']);
            $diskUsage = number_format($wiki->getFilesDiskUsage() / 1024 / 1024, 2);
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
